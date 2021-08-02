<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Attributes;
use App\Entity\Post;
use App\Repository\Exception\MultipleMatches;
use App\Util\FinderFactory;
use DateTimeImmutable;
use League\CommonMark\Extension\FrontMatter\Output\RenderedContentWithFrontMatter;
use League\CommonMark\MarkdownConverterInterface;
use Symfony\Component\Finder\SplFileInfo;

use function preg_match;
use function sprintf;

class PostRepository
{
    public function __construct(
        private FinderFactory $finderFactory,
        private string $postsPath,
        private MarkdownConverterInterface $markdownConverter,
    ) {
    }

    public function find(int $year, int $month, string $slug): ?Post
    {
        $files = ($this->finderFactory)()
            ->files()
            ->in($this->postsPath)
            ->name(sprintf("%d-%'.02d-*-%s.md", $year, $month, $slug));

        if ($files->count() > 1) {
            throw new MultipleMatches(sprintf(
                'More than one post matches for year: %d, month: %d, slug: %s',
                $year,
                $month,
                $slug,
            ));
        }

        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            return $this->convertToPost($file);
        }

        return null;
    }

    private function convertToPost(SplFileInfo $file): Post
    {
        $markdownContents = $this->markdownConverter->convertToHtml($file->getContents());
        $frontMatter = [];

        if ($markdownContents instanceof RenderedContentWithFrontMatter) {
            /** @var array<string, mixed> $frontMatter */
            $frontMatter = $markdownContents->getFrontMatter();
        }

        $attributes = new Attributes($frontMatter);

        /** @var string $publishDate */
        $publishDate = $frontMatter['publishDate'] ?? '';

        if ($publishDate === '' && preg_match('/^(\d{4}-\d{2}-\d{2})-.*/', $file->getFilename(), $matches)) {
            $publishDate = $matches[1] ?? '';
        }

        /** @var string $title */
        $title = $frontMatter['title'] ?? 'Untitled';

        return new Post(
            title: $title,
            content: $markdownContents->getContent(),
            publishDate: new DateTimeImmutable($publishDate),
            attributes: $attributes,
        );
    }
}
