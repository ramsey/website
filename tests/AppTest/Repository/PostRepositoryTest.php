<?php

declare(strict_types=1);

namespace AppTest\Repository;

use App\Repository\AuthorRepository;
use App\Repository\Exception\AuthorNotFound;
use App\Repository\Exception\MultipleMatches;
use App\Repository\PostRepository;
use App\Util\FinderFactory;
use ArrayIterator;
use League\CommonMark\MarkdownConverterInterface;
use Psr\Http\Message\UriFactoryInterface;
use Ramsey\Test\Website\TestCase;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Parser;

class PostRepositoryTest extends TestCase
{
    public function testFindByAttributesReturnsNull(): void
    {
        $finderFactory = $this->mockery(FinderFactory::class);
        $converter = $this->mockery(MarkdownConverterInterface::class);
        $authorRepository = $this->mockery(AuthorRepository::class);

        $repository = new PostRepository($finderFactory, '/path/to/files', $converter, $authorRepository);

        $post = $repository->findByAttributes(['year' => 2021]);

        $this->assertNull($post);
    }

    /**
     * @param array{year?: int, month?: int, slug?: string} $attributes
     *
     * @dataProvider findByAttributesPostsNotFoundProvider
     */
    public function testFindByAttributesReturnsNullWhenNoPostsFound(array $attributes): void
    {
        $finder = $this->mockery(Finder::class);
        $finder->expects()->files()->andReturnSelf();
        $finder->expects()->in('/path/to/files')->andReturnSelf();
        $finder->expects()->count()->andReturn(0);
        $finder->expects()->getIterator()->andReturn(new ArrayIterator([]));

        if (isset($attributes['month'])) {
            $finder
                ->expects()
                ->name('/^2021-07-\d{2}-a-nonexistent-test-post\.(md|html|markdown)$/')
                ->andReturnSelf();
        } else {
            $finder
                ->expects()
                ->name('/^2021-\d{2}-\d{2}-a-nonexistent-test-post\.(md|html|markdown)$/')
                ->andReturnSelf();
        }

        $finderFactory = $this->mockery(FinderFactory::class);
        $finderFactory->shouldReceive('__invoke')->andReturn($finder);

        $converter = $this->mockery(MarkdownConverterInterface::class);
        $authorRepository = $this->mockery(AuthorRepository::class);

        $repository = new PostRepository($finderFactory, '/path/to/files', $converter, $authorRepository);

        $this->assertNull($repository->findByAttributes($attributes));
    }

    /**
     * @param array{year?: int, month?: int, slug?: string} $attributes
     *
     * @dataProvider findByAttributesGeneralProvider
     */
    public function testFindByAttributesThrowsExceptionWhenMoreThanOnePostFound(array $attributes): void
    {
        $finder = $this->mockery(Finder::class);
        $finder->expects()->files()->andReturnSelf();
        $finder->expects()->in('/path/to/files')->andReturnSelf();
        $finder->expects()->count()->andReturn(2);

        $finderFactory = $this->mockery(FinderFactory::class);
        $finderFactory->shouldReceive('__invoke')->andReturn($finder);

        $converter = $this->mockery(MarkdownConverterInterface::class);
        $authorRepository = $this->mockery(AuthorRepository::class);

        $repository = new PostRepository($finderFactory, '/path/to/files', $converter, $authorRepository);

        $this->expectException(MultipleMatches::class);

        if (isset($attributes['month'])) {
            $finder
                ->expects()
                ->name('/^2021-07-\d{2}-a-test-post\.(md|html|markdown)$/')
                ->andReturnSelf();

            $this->expectExceptionMessage('More than one post matches for year: 2021, month: 7, slug: a-test-post');
        } else {
            $finder
                ->expects()
                ->name('/^2021-\d{2}-\d{2}-a-test-post\.(md|html|markdown)$/')
                ->andReturnSelf();

            $this->expectExceptionMessage('More than one post matches for year: 2021, slug: a-test-post');
        }

        $repository->findByAttributes($attributes);
    }

    /**
     * @param array{year?: int, month?: int, slug?: string} $attributes
     *
     * @dataProvider findByAttributesGeneralProvider
     */
    public function testFindByAttributesReturnsPost(array $attributes): void
    {
        $container = require __DIR__ . '/../../../config/container.php';

        $finderFactory = $container->get(FinderFactory::class);
        $converter = $container->get(MarkdownConverterInterface::class);
        $yamlParser = $container->get(Parser::class);
        $uriFactory = $container->get(UriFactoryInterface::class);

        $authorRepository = new AuthorRepository(
            $finderFactory,
            __DIR__ . '/../../stubs/authors',
            $yamlParser,
            $uriFactory,
        );

        $repository = new PostRepository(
            $finderFactory,
            __DIR__ . '/../../stubs/posts',
            $converter,
            $authorRepository,
            ['jsmith'],
        );

        $post = $repository->findByAttributes($attributes);

        $this->assertNotNull($post);
        $this->assertSame('Lorem ipsum dolor', $post->getTitle());
        $this->assertSame('2021-07-30 23:58:41', $post->getPublishDate()->format('Y-m-d H:i:s'));
        $this->assertSame(['foo', 'bar', 'baz'], $post->getAttributes()->get('tags'));
        $this->assertCount(2, $post->getAuthors());
    }

    /**
     * @param array{year?: int, month?: int, slug?: string} $attributes
     *
     * @dataProvider findByAttributesNoPublishDateProvider
     */
    public function testFindByAttributesReturnsPostUsingFileNameDateAsPublishDate(array $attributes): void
    {
        $container = require __DIR__ . '/../../../config/container.php';

        $finderFactory = $container->get(FinderFactory::class);
        $converter = $container->get(MarkdownConverterInterface::class);
        $authorRepository = $this->mockery(AuthorRepository::class);

        $repository = new PostRepository($finderFactory, __DIR__ . '/../../stubs/posts', $converter, $authorRepository);

        $post = $repository->findByAttributes($attributes);

        $this->assertNotNull($post);
        $this->assertSame('Lorem ipsum dolor', $post->getTitle());
        $this->assertSame('2021-07-29 00:00:00', $post->getPublishDate()->format('Y-m-d H:i:s'));
    }

    public function testFindByAttributesReturnsPostWithDefaultAuthor(): void
    {
        $container = require __DIR__ . '/../../../config/container.php';

        $finderFactory = $container->get(FinderFactory::class);
        $converter = $container->get(MarkdownConverterInterface::class);
        $yamlParser = $container->get(Parser::class);
        $uriFactory = $container->get(UriFactoryInterface::class);

        $authorRepository = new AuthorRepository(
            $finderFactory,
            __DIR__ . '/../../stubs/authors',
            $yamlParser,
            $uriFactory,
        );

        $repository = new PostRepository(
            $finderFactory,
            __DIR__ . '/../../stubs/posts',
            $converter,
            $authorRepository,
            ['jsmith'],
        );

        $post = $repository->findByAttributes([
            'year' => 2021,
            'slug' => 'no-publish-date',
        ]);

        $this->assertNotNull($post);
        $this->assertSame('Lorem ipsum dolor', $post->getTitle());
        $this->assertCount(1, $post->getAuthors());
    }

    public function testFindByAttributesThrowsExceptionWhenAuthorNotFound(): void
    {
        $container = require __DIR__ . '/../../../config/container.php';

        $finderFactory = $container->get(FinderFactory::class);
        $converter = $container->get(MarkdownConverterInterface::class);
        $yamlParser = $container->get(Parser::class);
        $uriFactory = $container->get(UriFactoryInterface::class);

        $authorRepository = new AuthorRepository(
            $finderFactory,
            __DIR__ . '/../../stubs/authors',
            $yamlParser,
            $uriFactory,
        );

        $repository = new PostRepository(
            $finderFactory,
            __DIR__ . '/../../stubs/posts',
            $converter,
            $authorRepository,
            ['foobar'],
        );

        $this->expectException(AuthorNotFound::class);
        $this->expectExceptionMessage("Unable to find author 'foobar'.");

        $repository->findByAttributes([
            'year' => 2021,
            'slug' => 'no-publish-date',
        ]);
    }

    /**
     * @return array<array{attributes: array{year?: int, month?: int, slug?: string}}>
     */
    public function findByAttributesPostsNotFoundProvider(): array
    {
        return [
            [
                'attributes' => [
                    'year' => 2021,
                    'month' => 7,
                    'slug' => 'a-nonexistent-test-post',
                ],
            ],
            [
                'attributes' => [
                    'year' => 2021,
                    'slug' => 'a-nonexistent-test-post',
                ],
            ],
        ];
    }

    /**
     * @return array<array{attributes: array{year?: int, month?: int, slug?: string}}>
     */
    public function findByAttributesGeneralProvider(): array
    {
        return [
            [
                'attributes' => [
                    'year' => 2021,
                    'month' => 7,
                    'slug' => 'a-test-post',
                ],
            ],
            [
                'attributes' => [
                    'year' => 2021,
                    'slug' => 'a-test-post',
                ],
            ],
        ];
    }

    /**
     * @return array<array{attributes: array{year?: int, month?: int, slug?: string}}>
     */
    public function findByAttributesNoPublishDateProvider(): array
    {
        return [
            [
                'attributes' => [
                    'year' => 2021,
                    'month' => 7,
                    'slug' => 'no-publish-date',
                ],
            ],
            [
                'attributes' => [
                    'year' => 2021,
                    'slug' => 'no-publish-date',
                ],
            ],
        ];
    }
}
