<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\Metadata;
use App\Entity\Page;
use App\Repository\MultipleMatchesException;
use App\Repository\PageRepository;
use App\Service\FinderFactory;
use App\Tests\TestCase;
use ArrayIterator;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class PageRepositoryTest extends TestCase
{
    public function testFindByAttributesReturnsNullWhenPageSlugIsNotPassed(): void
    {
        $finderFactory = $this->mockery(FinderFactory::class);
        $converter = new CommonMarkConverter();

        $repository = new PageRepository('/path/to/pages/data', $finderFactory, $converter);

        $this->assertNull($repository->findByAttributes([]));
    }

    public function testFindByAttributesReturnsNullWhenPageNotFound(): void
    {
        $finder = $this->mockery(Finder::class);
        $finderFactory = $this->mockery(FinderFactory::class);
        $converter = new CommonMarkConverter();

        $repository = new PageRepository('/path/to/pages/data', $finderFactory, $converter);

        $finderFactory->expects()->createFinder()->andReturns($finder);
        $finder->expects()->files()->andReturnSelf();
        $finder->expects()->in('/path/to/pages/data')->andReturnSelf();
        $finder->expects()->name('/^foo-page\.(md|markdown|html)$/')->andReturnSelf();
        $finder->expects()->count()->andReturns(0);
        $finder->expects()->getIterator()->andReturns(new ArrayIterator([]));

        $this->assertNull($repository->findByAttributes(['slug' => 'foo-page']));
    }

    public function testFindByAttributesThrowsExceptionForMultipleMatches(): void
    {
        $finder = $this->mockery(Finder::class);
        $finderFactory = $this->mockery(FinderFactory::class);
        $converter = new CommonMarkConverter();

        $repository = new PageRepository('/path/to/pages/data', $finderFactory, $converter);

        $finderFactory->expects()->createFinder()->andReturns($finder);
        $finder->expects()->files()->andReturnSelf();
        $finder->expects()->in('/path/to/pages/data')->andReturnSelf();
        $finder->expects()->name('/^foo-page\.(md|markdown|html)$/')->andReturnSelf();
        $finder->expects()->count()->andReturns(2);

        $this->expectException(MultipleMatchesException::class);
        $this->expectExceptionMessage('More than one page matches "foo-page"');

        $repository->findByAttributes(['slug' => 'foo-page']);
    }

    public function testFindByAttributesReturnsAuthorWithDefaultProperties(): void
    {
        $finder = $this->mockery(Finder::class);
        $finderFactory = $this->mockery(FinderFactory::class);
        $converter = new CommonMarkConverter();
        $file = $this->mockery(SplFileInfo::class);

        $repository = new PageRepository('/path/to/pages/data', $finderFactory, $converter);

        $finderFactory->expects()->createFinder()->andReturns($finder);
        $finder->expects()->files()->andReturnSelf();
        $finder->expects()->in('/path/to/pages/data')->andReturnSelf();
        $finder->expects()->name('/^about-page\.(md|markdown|html)$/')->andReturnSelf();
        $finder->expects()->count()->andReturns(0);
        $finder->expects()->getIterator()->andReturns(new ArrayIterator([$file]));

        $file->expects()->getContents()->andReturns('');

        $page = $repository->findByAttributes(['slug' => 'about-page']);

        $this->assertInstanceOf(Page::class, $page);
        $this->assertSame('Untitled', $page->title);
        $this->assertSame('', $page->content);
        $this->assertInstanceOf(Metadata::class, $page->metadata);
        $this->assertCount(0, $page->metadata);
    }

    public function testFindByAttributesReturnsAuthorWithPopulatedProperties(): void
    {
        $pageData = <<<'EOD'
            ---
            title: Some Page
            foo: bar
            baz: quux
            ---
            Lorem ipsum dolor sit amet, consectetur adipiscing elit.
            EOD;

        $finder = $this->mockery(Finder::class);
        $finderFactory = $this->mockery(FinderFactory::class);
        $converter = new CommonMarkConverter();
        $converter->getEnvironment()->addExtension(new FrontMatterExtension());
        $file = $this->mockery(SplFileInfo::class);

        $repository = new PageRepository('/path/to/pages/data', $finderFactory, $converter);

        $finderFactory->expects()->createFinder()->andReturns($finder);
        $finder->expects()->files()->andReturnSelf();
        $finder->expects()->in('/path/to/pages/data')->andReturnSelf();
        $finder->expects()->name('/^about-page\.(md|markdown|html)$/')->andReturnSelf();
        $finder->expects()->count()->andReturns(1);
        $finder->expects()->getIterator()->andReturns(new ArrayIterator([$file]));

        $file->expects()->getContents()->andReturns($pageData);

        $page = $repository->findByAttributes(['slug' => 'about-page']);

        $this->assertInstanceOf(Page::class, $page);
        $this->assertSame('Some Page', $page->title);
        $this->assertSame(
            "<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>\n",
            $page->content,
        );
        $this->assertInstanceOf(Metadata::class, $page->metadata);
        $this->assertCount(3, $page->metadata);
        $this->assertSame('Some Page', $page->metadata['title'] ?? null);
        $this->assertSame('bar', $page->metadata['foo'] ?? null);
        $this->assertSame('quux', $page->metadata['baz'] ?? null);
    }
}
