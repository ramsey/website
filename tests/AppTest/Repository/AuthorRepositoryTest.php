<?php

declare(strict_types=1);

namespace AppTest\Repository;

use App\Repository\AuthorRepository;
use App\Repository\Exception\MultipleMatches;
use App\Util\FinderFactory;
use ArrayIterator;
use Psr\Http\Message\UriFactoryInterface;
use Ramsey\Test\Website\TestCase;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Parser;

class AuthorRepositoryTest extends TestCase
{
    public function testFindByAttributesReturnsNullWhenNoAuthorsFound(): void
    {
        $finder = $this->mockery(Finder::class);
        $finder->expects()->files()->andReturnSelf();
        $finder->expects()->in('/path/to/files')->andReturnSelf();
        $finder->expects()->name('/^foobar\.(yaml|yml)$/')->andReturnSelf();
        $finder->expects()->count()->andReturn(0);
        $finder->expects()->getIterator()->andReturn(new ArrayIterator([]));

        $finderFactory = $this->mockery(FinderFactory::class);
        $finderFactory->shouldReceive('__invoke')->andReturn($finder);

        $yamlParser = $this->mockery(Parser::class);
        $uriFactory = $this->mockery(UriFactoryInterface::class);

        $repository = new AuthorRepository($finderFactory, '/path/to/files', $yamlParser, $uriFactory);

        $this->assertNull($repository->findByAttributes(['username' => 'foobar']));
    }

    public function testFindByAttributesReturnsNullWhenNoAuthorsPassed(): void
    {
        $finderFactory = $this->mockery(FinderFactory::class);
        $yamlParser = $this->mockery(Parser::class);
        $uriFactory = $this->mockery(UriFactoryInterface::class);

        $repository = new AuthorRepository($finderFactory, '/path/to/files', $yamlParser, $uriFactory);

        $this->assertNull($repository->findByAttributes([]));
    }

    public function testFindByAttributesThrowsExceptionWhenMoreThanOneAuthorFound(): void
    {
        $finder = $this->mockery(Finder::class);
        $finder->expects()->files()->andReturnSelf();
        $finder->expects()->in('/path/to/files')->andReturnSelf();
        $finder->expects()->name('/^foobar.*\.(yaml|yml)$/')->andReturnSelf();
        $finder->expects()->count()->andReturn(2);

        $finderFactory = $this->mockery(FinderFactory::class);
        $finderFactory->shouldReceive('__invoke')->andReturn($finder);

        $yamlParser = $this->mockery(Parser::class);
        $uriFactory = $this->mockery(UriFactoryInterface::class);

        $repository = new AuthorRepository($finderFactory, '/path/to/files', $yamlParser, $uriFactory);

        $this->expectException(MultipleMatches::class);
        $this->expectExceptionMessage('More than one author matches foobar.*');

        $repository->findByAttributes(['username' => 'foobar.*']);
    }

    public function testFindByAttributesReturnsAuthor(): void
    {
        $container = require __DIR__ . '/../../../config/container.php';

        /** @var FinderFactory $finderFactory */
        $finderFactory = $container->get(FinderFactory::class);

        /** @var Parser $yamlParser */
        $yamlParser = $container->get(Parser::class);

        /** @var UriFactoryInterface $uriFactory */
        $uriFactory = $container->get(UriFactoryInterface::class);

        $repository = new AuthorRepository($finderFactory, __DIR__ . '/../../stubs/authors', $yamlParser, $uriFactory);

        $author = $repository->findByAttributes(['username' => 'jdoe']);

        $this->assertNotNull($author);
        $this->assertSame('Jane Doe', $author->getName());
        $this->assertSame('https://example.com/jane', (string) $author->getUrl());
        $this->assertSame('https://example.com/jane.jpg', (string) $author->getImageUrl());
        $this->assertSame('jdoe@example.com', $author->getEmail());
        $this->assertSame(
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus '
            . 'gravida magna metus, sit amet condimentum purus molestie ac. '
            . 'Aliquam euismod velit non lacus iaculis, at commodo dolor '
            . 'viverra.',
            $author->getBiography(),
        );
        $this->assertFalse($author->getAttributes()->containsKey('name'));
        $this->assertFalse($author->getAttributes()->containsKey('email'));
        $this->assertFalse($author->getAttributes()->containsKey('url'));
        $this->assertFalse($author->getAttributes()->containsKey('imageUrl'));
        $this->assertFalse($author->getAttributes()->containsKey('biography'));
        $this->assertSame('bar', $author->getAttributes()->get('foo'));
        $this->assertSame('quux', $author->getAttributes()->get('baz'));
    }
}
