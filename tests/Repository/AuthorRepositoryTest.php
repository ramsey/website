<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\Author;
use App\Entity\Metadata;
use App\Repository\AuthorRepository;
use App\Repository\MultipleMatchesException;
use App\Service\FinderFactory;
use App\Tests\TestCase;
use ArrayIterator;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\UriInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Parser;

class AuthorRepositoryTest extends TestCase
{
    public function testFindByAttributesReturnsNullWhenUsernameIsNotPassed(): void
    {
        $finderFactory = $this->mockery(FinderFactory::class);
        $parser = new Parser();
        $uriFactory = new Psr17Factory();

        $repository = new AuthorRepository('/path/to/authors/data', $finderFactory, $parser, $uriFactory);

        $this->assertNull($repository->findByAttributes([]));
    }

    public function testFindByAttributesReturnsNullWhenAuthorNotFound(): void
    {
        $finder = $this->mockery(Finder::class);
        $finderFactory = $this->mockery(FinderFactory::class);
        $parser = new Parser();
        $uriFactory = new Psr17Factory();

        $repository = new AuthorRepository('/path/to/authors/data', $finderFactory, $parser, $uriFactory);

        $finderFactory->expects()->createFinder()->andReturns($finder);
        $finder->expects()->files()->andReturnSelf();
        $finder->expects()->in('/path/to/authors/data')->andReturnSelf();
        $finder->expects()->name('/^foobar\.(yaml|yml)$/')->andReturnSelf();
        $finder->expects()->count()->andReturns(0);
        $finder->expects()->getIterator()->andReturns(new ArrayIterator([]));

        $this->assertNull($repository->findByAttributes(['username' => 'foobar']));
    }

    public function testFindByAttributesThrowsExceptionForMultipleMatches(): void
    {
        $finder = $this->mockery(Finder::class);
        $finderFactory = $this->mockery(FinderFactory::class);
        $parser = new Parser();
        $uriFactory = new Psr17Factory();

        $repository = new AuthorRepository('/path/to/authors/data', $finderFactory, $parser, $uriFactory);

        $finderFactory->expects()->createFinder()->andReturns($finder);
        $finder->expects()->files()->andReturnSelf();
        $finder->expects()->in('/path/to/authors/data')->andReturnSelf();
        $finder->expects()->name('/^foobar\.(yaml|yml)$/')->andReturnSelf();
        $finder->expects()->count()->andReturns(2);

        $this->expectException(MultipleMatchesException::class);
        $this->expectExceptionMessage('More than one author matches "foobar"');

        $repository->findByAttributes(['username' => 'foobar']);
    }

    public function testFindByAttributesReturnsAuthorWithDefaultProperties(): void
    {
        $finder = $this->mockery(Finder::class);
        $finderFactory = $this->mockery(FinderFactory::class);
        $file = $this->mockery(SplFileInfo::class);
        $parser = new Parser();
        $uriFactory = new Psr17Factory();

        $repository = new AuthorRepository('/path/to/authors/data', $finderFactory, $parser, $uriFactory);

        $finderFactory->expects()->createFinder()->andReturns($finder);
        $finder->expects()->files()->andReturnSelf();
        $finder->expects()->in('/path/to/authors/data')->andReturnSelf();
        $finder->expects()->name('/^anAuthor\.(yaml|yml)$/')->andReturnSelf();
        $finder->expects()->count()->andReturns(0);
        $finder->expects()->getIterator()->andReturns(new ArrayIterator([$file]));

        $file->expects()->getContents()->andReturns('');
        $file->expects()->getFilenameWithoutExtension()->andReturns('baz');

        $author = $repository->findByAttributes(['username' => 'anAuthor']);

        $this->assertInstanceOf(Author::class, $author);
        $this->assertSame('baz', $author->name);
        $this->assertNull($author->biography);
        $this->assertNull($author->url);
        $this->assertNull($author->imageUrl);
        $this->assertNull($author->email);
        $this->assertInstanceOf(Metadata::class, $author->metadata);
        $this->assertCount(0, $author->metadata);
    }

    public function testFindByAttributesReturnsAuthorWithPopulatedProperties(): void
    {
        $authorData = <<<'EOD'
            name: Legolas
            email: legolas@example.com
            url: https://example.com
            imageUrl: https://example.com/legolas.jpg
            biography: >-
              Legolas is a Sindar Elf of the Woodland Realm and one of the nine
              members of the Fellowship who set out to destroy the One Ring.
            foo: bar
            baz: quux
            EOD;

        $finder = $this->mockery(Finder::class);
        $finderFactory = $this->mockery(FinderFactory::class);
        $file = $this->mockery(SplFileInfo::class);
        $parser = new Parser();
        $uriFactory = new Psr17Factory();

        $repository = new AuthorRepository('/path/to/authors/data', $finderFactory, $parser, $uriFactory);

        $finderFactory->expects()->createFinder()->andReturns($finder);
        $finder->expects()->files()->andReturnSelf();
        $finder->expects()->in('/path/to/authors/data')->andReturnSelf();
        $finder->expects()->name('/^legolas\.(yaml|yml)$/')->andReturnSelf();
        $finder->expects()->count()->andReturns(1);
        $finder->expects()->getIterator()->andReturns(new ArrayIterator([$file]));

        $file->expects()->getContents()->andReturns($authorData);

        $author = $repository->findByAttributes(['username' => 'legolas']);

        $this->assertInstanceOf(Author::class, $author);
        $this->assertSame('Legolas', $author->name);
        $this->assertSame(
            'Legolas is a Sindar Elf of the Woodland Realm and one of the nine '
                . 'members of the Fellowship who set out to destroy the One Ring.',
            $author->biography,
        );
        $this->assertInstanceOf(UriInterface::class, $author->url);
        $this->assertSame('https://example.com', (string) $author->url);
        $this->assertInstanceOf(UriInterface::class, $author->imageUrl);
        $this->assertSame('https://example.com/legolas.jpg', (string) $author->imageUrl);
        $this->assertSame('legolas@example.com', $author->email);
        $this->assertInstanceOf(Metadata::class, $author->metadata);
        $this->assertCount(2, $author->metadata);
        $this->assertSame('bar', $author->metadata['foo'] ?? null);
        $this->assertSame('quux', $author->metadata['baz'] ?? null);
    }
}
