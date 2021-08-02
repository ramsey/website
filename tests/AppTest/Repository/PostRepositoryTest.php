<?php

declare(strict_types=1);

namespace AppTest\Repository;

use App\Repository\Exception\MultipleMatches;
use App\Repository\PostRepository;
use App\Util\FinderFactory;
use ArrayIterator;
use League\CommonMark\MarkdownConverterInterface;
use Ramsey\Test\Website\TestCase;
use Symfony\Component\Finder\Finder;

class PostRepositoryTest extends TestCase
{
    public function testFindReturnsNullWhenNoPostsFound(): void
    {
        $finder = $this->mockery(Finder::class);
        $finder->expects()->files()->andReturnSelf();
        $finder->expects()->in('/path/to/files')->andReturnSelf();
        $finder->expects()->name('2021-07-*-a-nonexistent-test-post.md')->andReturnSelf();
        $finder->expects()->count()->andReturn(0);
        $finder->expects()->getIterator()->andReturn(new ArrayIterator([]));

        $finderFactory = $this->mockery(FinderFactory::class);
        $finderFactory->shouldReceive('__invoke')->andReturn($finder);

        $converter = $this->mockery(MarkdownConverterInterface::class);

        $repository = new PostRepository($finderFactory, '/path/to/files', $converter);

        $this->assertNull($repository->find(2021, 7, 'a-nonexistent-test-post'));
    }

    public function testFindThrowsExceptionWhenMoreThanOnePostFound(): void
    {
        $finder = $this->mockery(Finder::class);
        $finder->expects()->files()->andReturnSelf();
        $finder->expects()->in('/path/to/files')->andReturnSelf();
        $finder->expects()->name('2021-07-*-a-test-post.md')->andReturnSelf();
        $finder->expects()->count()->andReturn(2);

        $finderFactory = $this->mockery(FinderFactory::class);
        $finderFactory->shouldReceive('__invoke')->andReturn($finder);

        $converter = $this->mockery(MarkdownConverterInterface::class);

        $repository = new PostRepository($finderFactory, '/path/to/files', $converter);

        $this->expectException(MultipleMatches::class);
        $this->expectExceptionMessage('More than one post matches for year: 2021, month: 7, slug: a-test-post');

        $repository->find(2021, 7, 'a-test-post');
    }

    public function testFindReturnsPost(): void
    {
        $container = require __DIR__ . '/../../../config/container.php';

        /** @var FinderFactory $finderFactory */
        $finderFactory = $container->get(FinderFactory::class);

        /** @var MarkdownConverterInterface $converter */
        $converter = $container->get(MarkdownConverterInterface::class);

        $repository = new PostRepository($finderFactory, __DIR__ . '/../../stubs/posts', $converter);

        $post = $repository->find(2021, 7, 'a-test-post');

        $this->assertNotNull($post);
        $this->assertSame('Lorem ipsum dolor', $post->getTitle());
        $this->assertSame('2021-07-30 23:58:41', $post->getPublishDate()->format('Y-m-d H:i:s'));
        $this->assertSame(['foo', 'bar', 'baz'], $post->getAttributes()->get('tags'));
    }

    public function testFindReturnsPostUsingFileNameDateAsPublishDate(): void
    {
        $container = require __DIR__ . '/../../../config/container.php';

        /** @var FinderFactory $finderFactory */
        $finderFactory = $container->get(FinderFactory::class);

        /** @var MarkdownConverterInterface $converter */
        $converter = $container->get(MarkdownConverterInterface::class);

        $repository = new PostRepository($finderFactory, __DIR__ . '/../../stubs/posts', $converter);

        $post = $repository->find(2021, 7, 'no-publish-date');

        $this->assertNotNull($post);
        $this->assertSame('Lorem ipsum dolor', $post->getTitle());
        $this->assertSame('2021-07-29 00:00:00', $post->getPublishDate()->format('Y-m-d H:i:s'));
    }
}
