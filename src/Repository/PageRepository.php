<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Metadata;
use App\Entity\Page;
use App\Service\FinderFactory;
use League\CommonMark\ConverterInterface;
use League\CommonMark\Extension\FrontMatter\Output\RenderedContentWithFrontMatter;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Finder\SplFileInfo;

use function sprintf;

/**
 * @psalm-type PageFrontMatter = array{title?: string}
 */
final class PageRepository
{
    private const FILENAME_PATTERN = '/^%s\.(md|markdown|html)$/';

    public function __construct(
        #[Autowire('%app.content.paths.pagesPath%')] private readonly string $pagesPath,
        private readonly FinderFactory $finderFactory,
        private readonly ConverterInterface $converter,
    ) {
    }

    /**
     * @param array{slug?: string} $attributes
     *
     * @throws MultipleMatchesException
     */
    public function findByAttributes(array $attributes): ?Page
    {
        if (isset($attributes['slug'])) {
            return $this->findBySlug($attributes['slug']);
        }

        return null;
    }

    private function findBySlug(string $slug): ?Page
    {
        $files = $this->finderFactory->createFinder()
            ->files()
            ->in($this->pagesPath)
            ->name(sprintf(self::FILENAME_PATTERN, $slug));

        if ($files->count() > 1) {
            throw new MultipleMatchesException(sprintf(
                'More than one page matches "%s"',
                $slug,
            ));
        }

        foreach ($files as $file) {
            return $this->convertToPage($file);
        }

        return null;
    }

    private function convertToPage(SplFileInfo $file): Page
    {
        $markdown = $this->converter->convert($file->getContents());
        $frontMatter = [];

        if ($markdown instanceof RenderedContentWithFrontMatter) {
            /** @var PageFrontMatter $frontMatter */
            $frontMatter = $markdown->getFrontMatter() ?: [];
        }

        return new Page(
            title: $frontMatter['title'] ?? 'Untitled',
            content: $markdown->getContent(),
            metadata: new Metadata($frontMatter),
        );
    }
}
