<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\AuthorCollection;
use App\Entity\BlogPost;
use App\Entity\Metadata;
use App\Repository\AuthorNotFoundException;
use App\Repository\AuthorRepository;
use App\Repository\BlogPostRepository;
use App\Repository\MultipleMatchesException;
use App\Service\FinderFactory;
use App\Tests\TestCase;
use ArrayIterator;
use DateTimeImmutable;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;
use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Parser;

use function date;

class BlogPostRepositoryTest extends TestCase
{
    public function testFindByAttributesReturnsNullWhenRecognizedAttributesAreNotPassed(): void
    {
        $finderFactory = $this->mockery(FinderFactory::class);
        $converter = new CommonMarkConverter();
        $parser = new Parser();
        $uriFactory = new Psr17Factory();
        $authorRepo = new AuthorRepository('/path/to/authors/data', $finderFactory, $parser, $uriFactory);

        $repository = new BlogPostRepository(
            '/path/to/blog/data',
            ['foo', 'bar'],
            $authorRepo,
            $finderFactory,
            $converter,
        );

        $this->assertNull($repository->findByAttributes([]));
    }

    public function testFindByAttributesReturnsNullWhenPostIsNotFoundForYearAndMonthAndSlug(): void
    {
        $finder = $this->mockery(Finder::class);
        $finderFactory = $this->mockery(FinderFactory::class);
        $converter = new CommonMarkConverter();
        $parser = new Parser();
        $uriFactory = new Psr17Factory();
        $authorRepo = new AuthorRepository('/path/to/authors/data', $finderFactory, $parser, $uriFactory);

        $repository = new BlogPostRepository(
            '/path/to/blog/data',
            ['foo', 'bar'],
            $authorRepo,
            $finderFactory,
            $converter,
        );

        $finderFactory->expects()->createFinder()->andReturns($finder);
        $finder->expects()->files()->andReturnSelf();
        $finder->expects()->in('/path/to/blog/data')->andReturnSelf();
        $finder->expects()->name('/^2022-08-\d{2}-blog-post\.(md|html|markdown)$/')->andReturnSelf();
        $finder->expects()->count()->andReturns(0);
        $finder->expects()->getIterator()->andReturns(new ArrayIterator([]));

        $this->assertNull(
            $repository->findByAttributes([
                'year' => 2022,
                'month' => 8,
                'slug' => 'blog-post',
            ]),
        );
    }

    public function testFindByAttributesReturnsNullWhenPostIsNotFoundForYearAndSlug(): void
    {
        $finder = $this->mockery(Finder::class);
        $finderFactory = $this->mockery(FinderFactory::class);
        $converter = new CommonMarkConverter();
        $parser = new Parser();
        $uriFactory = new Psr17Factory();
        $authorRepo = new AuthorRepository('/path/to/authors/data', $finderFactory, $parser, $uriFactory);

        $repository = new BlogPostRepository(
            '/path/to/blog/data',
            ['foo', 'bar'],
            $authorRepo,
            $finderFactory,
            $converter,
        );

        $finderFactory->expects()->createFinder()->andReturns($finder);
        $finder->expects()->files()->andReturnSelf();
        $finder->expects()->in('/path/to/blog/data')->andReturnSelf();
        $finder->expects()->name('/^2022-\d{2}-\d{2}-blog-post\.(md|html|markdown)$/')->andReturnSelf();
        $finder->expects()->count()->andReturns(0);
        $finder->expects()->getIterator()->andReturns(new ArrayIterator([]));

        $this->assertNull(
            $repository->findByAttributes([
                'year' => 2022,
                'slug' => 'blog-post',
            ]),
        );
    }

    public function testFindByAttributesThrowsExceptionWhenMultipleMatchesFoundForYearAndMonthAndSlug(): void
    {
        $finder = $this->mockery(Finder::class);
        $finderFactory = $this->mockery(FinderFactory::class);
        $converter = new CommonMarkConverter();
        $parser = new Parser();
        $uriFactory = new Psr17Factory();
        $authorRepo = new AuthorRepository('/path/to/authors/data', $finderFactory, $parser, $uriFactory);

        $repository = new BlogPostRepository(
            '/path/to/blog/data',
            ['foo', 'bar'],
            $authorRepo,
            $finderFactory,
            $converter,
        );

        $finderFactory->expects()->createFinder()->andReturns($finder);
        $finder->expects()->files()->andReturnSelf();
        $finder->expects()->in('/path/to/blog/data')->andReturnSelf();
        $finder->expects()->name('/^2022-08-\d{2}-blog-post\.(md|html|markdown)$/')->andReturnSelf();
        $finder->expects()->count()->andReturns(2);

        $this->expectException(MultipleMatchesException::class);
        $this->expectExceptionMessage(
            'More than one post matches for year: 2022, month: 8, slug: blog-post',
        );

        $repository->findByAttributes([
            'year' => 2022,
            'month' => 8,
            'slug' => 'blog-post',
        ]);
    }

    public function testFindByAttributesThrowsExceptionWhenMultipleMatchesFoundForYearAndSlug(): void
    {
        $finder = $this->mockery(Finder::class);
        $finderFactory = $this->mockery(FinderFactory::class);
        $converter = new CommonMarkConverter();
        $parser = new Parser();
        $uriFactory = new Psr17Factory();
        $authorRepo = new AuthorRepository('/path/to/authors/data', $finderFactory, $parser, $uriFactory);

        $repository = new BlogPostRepository(
            '/path/to/blog/data',
            ['foo', 'bar'],
            $authorRepo,
            $finderFactory,
            $converter,
        );

        $finderFactory->expects()->createFinder()->andReturns($finder);
        $finder->expects()->files()->andReturnSelf();
        $finder->expects()->in('/path/to/blog/data')->andReturnSelf();
        $finder->expects()->name('/^2022-\d{2}-\d{2}-blog-post\.(md|html|markdown)$/')->andReturnSelf();
        $finder->expects()->count()->andReturns(2);

        $this->expectException(MultipleMatchesException::class);
        $this->expectExceptionMessage(
            'More than one post matches for year: 2022, slug: blog-post',
        );

        $repository->findByAttributes([
            'year' => 2022,
            'slug' => 'blog-post',
        ]);
    }

    public function testFindByAttributesForYearAndMonthAndSlugReturnsBlogPostWithDefaultProperties(): void
    {
        $finder = $this->mockery(Finder::class);
        $finderFactory = $this->mockery(FinderFactory::class);
        $converter = new CommonMarkConverter();
        $parser = new Parser();
        $uriFactory = new Psr17Factory();
        $authorRepo = new AuthorRepository('/path/to/authors/data', $finderFactory, $parser, $uriFactory);
        $file = $this->mockery(SplFileInfo::class);

        $repository = new BlogPostRepository(
            '/path/to/blog/data',
            [],
            $authorRepo,
            $finderFactory,
            $converter,
        );

        $finderFactory->expects()->createFinder()->andReturns($finder);
        $finder->expects()->files()->andReturnSelf();
        $finder->expects()->in('/path/to/blog/data')->andReturnSelf();
        $finder->expects()->name('/^2022-08-\d{2}-blog-post\.(md|html|markdown)$/')->andReturnSelf();
        $finder->expects()->count()->andReturns(0);
        $finder->expects()->getIterator()->andReturns(new ArrayIterator([$file]));

        $file->expects()->getContents()->andReturns('');
        $file->expects()->getFilename()->andReturns('blog-post');

        $blogPost = $repository->findByAttributes([
            'year' => 2022,
            'month' => 8,
            'slug' => 'blog-post',
        ]);

        $this->assertInstanceOf(BlogPost::class, $blogPost);
        $this->assertSame('Untitled', $blogPost->title);
        $this->assertSame('', $blogPost->content);
        $this->assertInstanceOf(DateTimeImmutable::class, $blogPost->published);
        $this->assertSame(date('Y-m-d'), $blogPost->published->format('Y-m-d'));
        $this->assertInstanceOf(AuthorCollection::class, $blogPost->authors);
        $this->assertCount(0, $blogPost->authors);
        $this->assertInstanceOf(Metadata::class, $blogPost->metadata);
        $this->assertCount(0, $blogPost->metadata);
        $this->assertNull($blogPost->lastUpdated);
    }

    public function testFindByAttributesForYearAndSlugReturnsBlogPostWithPopulatedProperties(): void
    {
        $author1Data = <<<'EOD'
            name: Frodo Baggins
            EOD;

        $author2Data = <<<'EOD'
            name: Samwise Gamgee
            EOD;

        $blogPostData = <<<'EOD'
            ---
            title: Some Blog Post
            published: Wed, 15 Sep 2021 14:23:36 +0000
            lastUpdated: Tue, 01 Mar 2022 23:18:09 +0000
            authors: [frodo, samwise]
            ---
            Lorem ipsum dolor sit amet, consectetur adipiscing elit.
            EOD;

        $blogFinder = $this->mockery(Finder::class);
        $blogFinderFactory = $this->mockery(FinderFactory::class);
        $authorFinder = $this->mockery(Finder::class);
        $authorFinderFactory = $this->mockery(FinderFactory::class);
        $converter = new CommonMarkConverter();
        $converter->getEnvironment()->addExtension(new FrontMatterExtension());
        $parser = new Parser();
        $uriFactory = new Psr17Factory();
        $authorRepo = new AuthorRepository('/path/to/authors/data', $authorFinderFactory, $parser, $uriFactory);
        $blogPostFile = $this->mockery(SplFileInfo::class);
        $author1File = $this->mockery(SplFileInfo::class);
        $author2File = $this->mockery(SplFileInfo::class);

        $repository = new BlogPostRepository(
            '/path/to/blog/data',
            [],
            $authorRepo,
            $blogFinderFactory,
            $converter,
        );

        $blogFinderFactory->expects()->createFinder()->andReturns($blogFinder);
        $blogFinder->expects()->files()->andReturnSelf();
        $blogFinder->expects()->in('/path/to/blog/data')->andReturnSelf();
        $blogFinder->expects()->name('/^2022-\d{2}-\d{2}-blog-post\.(md|html|markdown)$/')->andReturnSelf();
        $blogFinder->expects()->count()->andReturns(0);
        $blogFinder->expects()->getIterator()->andReturns(new ArrayIterator([$blogPostFile]));

        $blogPostFile->expects()->getContents()->andReturns($blogPostData);

        $authorFinderFactory->expects()->createFinder()->twice()->andReturns($authorFinder);
        $authorFinder->expects()->files()->twice()->andReturnSelf();
        $authorFinder->expects()->in('/path/to/authors/data')->twice()->andReturnSelf();
        $authorFinder->expects()->name('/^frodo\.(yaml|yml)$/')->andReturnSelf();
        $authorFinder->expects()->name('/^samwise\.(yaml|yml)$/')->andReturnSelf();
        $authorFinder->expects()->count()->twice()->andReturns(1);
        $authorFinder->expects()->getIterator()->twice()->andReturns(
            new ArrayIterator([$author1File]),
            new ArrayIterator([$author2File]),
        );

        $author1File->expects()->getContents()->andReturns($author1Data);
        $author2File->expects()->getContents()->andReturns($author2Data);

        $blogPost = $repository->findByAttributes([
            'year' => 2022,
            'slug' => 'blog-post',
        ]);

        $this->assertInstanceOf(BlogPost::class, $blogPost);
        $this->assertSame('Some Blog Post', $blogPost->title);
        $this->assertSame("<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>\n", $blogPost->content);
        $this->assertInstanceOf(DateTimeImmutable::class, $blogPost->published);
        $this->assertSame('2021-09-15T14:23:36+00:00', $blogPost->published->format('c'));
        $this->assertInstanceOf(AuthorCollection::class, $blogPost->authors);
        $this->assertCount(2, $blogPost->authors);
        $this->assertSame('Frodo Baggins', $blogPost->authors[0]?->name);
        $this->assertSame('Samwise Gamgee', $blogPost->authors[1]?->name);
        $this->assertInstanceOf(Metadata::class, $blogPost->metadata);
        $this->assertCount(4, $blogPost->metadata);
        $this->assertInstanceOf(DateTimeImmutable::class, $blogPost->lastUpdated);
        $this->assertSame('2022-03-01T23:18:09+00:00', $blogPost->lastUpdated->format('c'));
    }

    public function testFindByAttributesForYearAndSlugReturnsBlogPostWithPublishedDateFromFileName(): void
    {
        $blogPostData = <<<'EOD'
            ---
            lastUpdated: 2022-03-01T23:18:09+00:00
            ---

            EOD;

        $blogFinder = $this->mockery(Finder::class);
        $blogFinderFactory = $this->mockery(FinderFactory::class);
        $converter = new CommonMarkConverter();
        $converter->getEnvironment()->addExtension(new FrontMatterExtension());
        $parser = new Parser();
        $uriFactory = new Psr17Factory();
        $authorRepo = new AuthorRepository('/path/to/authors/data', $blogFinderFactory, $parser, $uriFactory);
        $blogPostFile = $this->mockery(SplFileInfo::class);

        $repository = new BlogPostRepository(
            '/path/to/blog/data',
            [],
            $authorRepo,
            $blogFinderFactory,
            $converter,
        );

        $blogFinderFactory->expects()->createFinder()->andReturns($blogFinder);
        $blogFinder->expects()->files()->andReturnSelf();
        $blogFinder->expects()->in('/path/to/blog/data')->andReturnSelf();
        $blogFinder->expects()->name('/^2020-\d{2}-\d{2}-blog-post\.(md|html|markdown)$/')->andReturnSelf();
        $blogFinder->expects()->count()->andReturns(0);
        $blogFinder->expects()->getIterator()->andReturns(new ArrayIterator([$blogPostFile]));

        $blogPostFile->expects()->getContents()->andReturns($blogPostData);
        $blogPostFile->expects()->getFilename()->andReturns('2020-03-01-blog-post');

        $blogPost = $repository->findByAttributes([
            'year' => 2020,
            'slug' => 'blog-post',
        ]);

        $this->assertInstanceOf(BlogPost::class, $blogPost);
        $this->assertSame('Untitled', $blogPost->title);
        $this->assertSame('', $blogPost->content);
        $this->assertInstanceOf(DateTimeImmutable::class, $blogPost->published);
        $this->assertSame('2020-03-01T00:00:00+00:00', $blogPost->published->format('c'));
        $this->assertInstanceOf(AuthorCollection::class, $blogPost->authors);
        $this->assertCount(0, $blogPost->authors);
        $this->assertInstanceOf(Metadata::class, $blogPost->metadata);
        $this->assertCount(1, $blogPost->metadata);
        $this->assertInstanceOf(DateTimeImmutable::class, $blogPost->lastUpdated);
        $this->assertSame('Tue, 01 Mar 2022 23:18:09 +0000', $blogPost->lastUpdated->format('r'));
    }

    public function testFindByAttributesThrowsExceptionWhenAuthorCannotBeFound(): void
    {
        $blogPostData = <<<'EOD'
            ---
            title: Some Blog Post
            published: Wed, 15 Sep 2021 14:23:36 +0000
            lastUpdated: Tue, 01 Mar 2022 23:18:09 +0000
            authors: [gandalf]
            ---
            Lorem ipsum dolor sit amet, consectetur adipiscing elit.
            EOD;

        $blogFinder = $this->mockery(Finder::class);
        $blogFinderFactory = $this->mockery(FinderFactory::class);
        $authorFinder = $this->mockery(Finder::class);
        $authorFinderFactory = $this->mockery(FinderFactory::class);
        $converter = new CommonMarkConverter();
        $converter->getEnvironment()->addExtension(new FrontMatterExtension());
        $parser = new Parser();
        $uriFactory = new Psr17Factory();
        $authorRepo = new AuthorRepository('/path/to/authors/data', $authorFinderFactory, $parser, $uriFactory);
        $blogPostFile = $this->mockery(SplFileInfo::class);

        $repository = new BlogPostRepository(
            '/path/to/blog/data',
            [],
            $authorRepo,
            $blogFinderFactory,
            $converter,
        );

        $blogFinderFactory->expects()->createFinder()->andReturns($blogFinder);
        $blogFinder->expects()->files()->andReturnSelf();
        $blogFinder->expects()->in('/path/to/blog/data')->andReturnSelf();
        $blogFinder->expects()->name('/^2022-\d{2}-\d{2}-blog-post\.(md|html|markdown)$/')->andReturnSelf();
        $blogFinder->expects()->count()->andReturns(0);
        $blogFinder->expects()->getIterator()->andReturns(new ArrayIterator([$blogPostFile]));

        $blogPostFile->expects()->getContents()->andReturns($blogPostData);

        $authorFinderFactory->expects()->createFinder()->andReturns($authorFinder);
        $authorFinder->expects()->files()->andReturnSelf();
        $authorFinder->expects()->in('/path/to/authors/data')->andReturnSelf();
        $authorFinder->expects()->name('/^gandalf\.(yaml|yml)$/')->andReturnSelf();
        $authorFinder->expects()->count()->andReturns(1);
        $authorFinder->expects()->getIterator()->andReturns(new ArrayIterator([]));

        $this->expectException(AuthorNotFoundException::class);
        $this->expectExceptionMessage('Unable to find author \'gandalf\'.');

        $repository->findByAttributes([
            'year' => 2022,
            'slug' => 'blog-post',
        ]);
    }
}
