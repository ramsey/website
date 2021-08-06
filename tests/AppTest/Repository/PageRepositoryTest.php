<?php

declare(strict_types=1);

namespace AppTest\Repository;

use App\Repository\Exception\MultipleMatches;
use App\Repository\PageRepository;
use App\Util\FinderFactory;
use ArrayIterator;
use League\CommonMark\MarkdownConverterInterface;
use Ramsey\Test\Website\TestCase;
use Symfony\Component\Finder\Finder;

use function trim;

class PageRepositoryTest extends TestCase
{
    public function testFindReturnsNullWhenNoPagesFound(): void
    {
        $finder = $this->mockery(Finder::class);
        $finder->expects()->files()->andReturnSelf();
        $finder->expects()->in('/path/to/files')->andReturnSelf();
        $finder->expects()->name('/^a-nonexistent-page\.(md|markdown|html)$/')->andReturnSelf();
        $finder->expects()->count()->andReturn(0);
        $finder->expects()->getIterator()->andReturn(new ArrayIterator([]));

        $finderFactory = $this->mockery(FinderFactory::class);
        $finderFactory->shouldReceive('__invoke')->andReturn($finder);

        $converter = $this->mockery(MarkdownConverterInterface::class);

        $repository = new PageRepository($finderFactory, '/path/to/files', $converter);

        $this->assertNull($repository->find('a-nonexistent-page'));
    }

    public function testFindThrowsExceptionWhenMoreThanOnePageFound(): void
    {
        $finder = $this->mockery(Finder::class);
        $finder->expects()->files()->andReturnSelf();
        $finder->expects()->in('/path/to/files')->andReturnSelf();
        $finder->expects()->name('/^a-test-post\.(md|markdown|html)$/')->andReturnSelf();
        $finder->expects()->count()->andReturn(2);

        $finderFactory = $this->mockery(FinderFactory::class);
        $finderFactory->shouldReceive('__invoke')->andReturn($finder);

        $converter = $this->mockery(MarkdownConverterInterface::class);

        $repository = new PageRepository($finderFactory, '/path/to/files', $converter);

        $this->expectException(MultipleMatches::class);
        $this->expectExceptionMessage('More than one page matches a-test-post');

        $repository->find('a-test-post');
    }

    public function testFindReturnsPage(): void
    {
        $container = require __DIR__ . '/../../../config/container.php';

        /** @var FinderFactory $finderFactory */
        $finderFactory = $container->get(FinderFactory::class);

        /** @var MarkdownConverterInterface $converter */
        $converter = $container->get(MarkdownConverterInterface::class);

        $repository = new PageRepository($finderFactory, __DIR__ . '/../../stubs/pages', $converter);

        $page = $repository->find('about-me');

        $this->assertNotNull($page);
        $this->assertSame('About Me', $page->getTitle());
        $this->assertSame('<h1>Hello!</h1>', trim($page->getContent()));
        $this->assertTrue($page->getAttributes()->containsKey('permalink'));
        $this->assertSame('/about-me/', $page->getAttributes()->get('permalink'));
        $this->assertTrue($page->getAttributes()->containsKey('keywords'));
        $this->assertSame(['foo', 'bar', 'baz'], $page->getAttributes()->get('keywords'));
    }
}
