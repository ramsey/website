<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AuthorCollection;
use App\Entity\BlogPost;
use App\Entity\BlogPostCollection;
use App\Entity\Metadata;
use App\Service\FinderFactory;
use DateTimeImmutable;
use DomainException;
use Exception;
use League\CommonMark\ConverterInterface;
use League\CommonMark\Extension\FrontMatter\Output\RenderedContentWithFrontMatter;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Finder\SplFileInfo;

use function count;
use function is_int;
use function preg_match;
use function sprintf;
use function strtotime;
use function time;

/**
 * @psalm-type BlogPostFrontMatter = array{title?: string, authors?: string[], published?: int | string, date?: int | string, lastUpdated?: int | string, updated?: int | string}
 */
final class BlogPostRepository
{
    private const FILE_DATE_PATTERN = '/.*(\d{4}-\d{2}-\d{2}).*/';
    private const FILE_SLUG_PATTERN = '/^\d{4}-\d{2}-\d{2}-(.*)\.(md|html|markdown)$/';
    private const FILE_YEAR_SLUG_PATTERN = '/^%d-\d{2}-\d{2}-%s\.(md|html|markdown)$/';
    private const FILE_YEAR_MONTH_SLUG_PATTERN = "/^%d-%'.02d-\d{2}-%s\.(md|html|markdown)$/";

    /**
     * @param string[] $defaultAuthors
     */
    public function __construct(
        #[Autowire('%app.content.paths.blogPostsPath%')] private readonly string $blogPostsPath,
        #[Autowire('%app.content.defaultAuthors%')] private readonly array $defaultAuthors,
        private readonly AuthorRepository $authorRepository,
        private readonly FinderFactory $finderFactory,
        private readonly ConverterInterface $converter,
    ) {
    }

    /**
     * @throws Exception
     */
    public function findAll(): BlogPostCollection
    {
        $collection = new BlogPostCollection();

        $files = $this->finderFactory->createFinder()
            ->files()
            ->in($this->blogPostsPath)
            ->sortByName(true)
            ->reverseSorting();

        foreach ($files as $file) {
            $collection[] = $this->convertToBlogPost($file);
        }

        return $collection;
    }

    /**
     * @param array{year?: int, month?: int, slug?: string} $attributes
     *
     * @throws Exception
     * @throws MultipleMatchesException
     */
    public function findByAttributes(array $attributes): ?BlogPost
    {
        if (isset($attributes['year']) && isset($attributes['month']) && isset($attributes['slug'])) {
            return $this->findByYearMonthSlug(
                $attributes['year'],
                $attributes['month'],
                $attributes['slug'],
            );
        }

        if (isset($attributes['year']) && isset($attributes['slug'])) {
            return $this->findByYearSlug(
                $attributes['year'],
                $attributes['slug'],
            );
        }

        return null;
    }

    /**
     * @throws Exception
     */
    private function findByYearMonthSlug(int $year, int $month, string $slug): ?BlogPost
    {
        $files = $this->finderFactory->createFinder()
            ->files()
            ->in($this->blogPostsPath)
            ->name(sprintf(self::FILE_YEAR_MONTH_SLUG_PATTERN, $year, $month, $slug));

        if ($files->count() > 1) {
            throw new MultipleMatchesException(sprintf(
                'More than one post matches for year: %d, month: %d, slug: %s',
                $year,
                $month,
                $slug,
            ));
        }

        foreach ($files as $file) {
            return $this->convertToBlogPost($file);
        }

        return null;
    }

    /**
     * @throws Exception
     */
    private function findByYearSlug(int $year, string $slug): ?BlogPost
    {
        $files = $this->finderFactory->createFinder()
            ->files()
            ->in($this->blogPostsPath)
            ->name(sprintf(self::FILE_YEAR_SLUG_PATTERN, $year, $slug));

        if ($files->count() > 1) {
            throw new MultipleMatchesException(sprintf(
                'More than one post matches for year: %d, slug: %s',
                $year,
                $slug,
            ));
        }

        foreach ($files as $file) {
            return $this->convertToBlogPost($file);
        }

        return null;
    }

    /**
     * @throws Exception
     */
    private function convertToBlogPost(SplFileInfo $file): BlogPost
    {
        if (!preg_match(self::FILE_SLUG_PATTERN, $file->getFilename(), $matches)) {
            throw new DomainException(sprintf(
                'Unable to parse slug from file name "%s"',
                $file->getFilename(),
            ));
        }

        $slug = $matches[1];

        $markdown = $this->converter->convert($file->getContents());

        /** @var BlogPostFrontMatter $frontMatter */
        $frontMatter = [];

        if ($markdown instanceof RenderedContentWithFrontMatter) {
            /** @var BlogPostFrontMatter $frontMatter */
            $frontMatter = $markdown->getFrontMatter() ?: [];
        }

        // If "published" exists, use it; otherwise, check for "date."
        $published = $frontMatter['published'] ?? $frontMatter['date'] ?? -1;
        if ($published === -1) {
            preg_match(self::FILE_DATE_PATTERN, $file->getFilename(), $matches);
            $published = isset($matches[1]) ? (int) strtotime($matches[1]) : time();
        }

        if (is_int($published)) {
            $published = "@$published";
        }

        // If "lastUpdated" exists, use it; otherwise, check for "updated."
        $lastUpdated = $frontMatter['lastUpdated'] ?? $frontMatter['updated'] ?? null;

        if (is_int($lastUpdated)) {
            $lastUpdated = "@$lastUpdated";
        }

        return new BlogPost(
            title: $frontMatter['title'] ?? 'Untitled',
            content: $markdown->getContent(),
            published: new DateTimeImmutable($published),
            slug: $slug,
            authors: $this->getAuthors($frontMatter['authors'] ?? []),
            metadata: new Metadata($frontMatter),
            lastUpdated: $lastUpdated ? new DateTimeImmutable($lastUpdated) : null,
        );
    }

    /**
     * @param string[] $authorUsernames
     */
    private function getAuthors(array $authorUsernames): AuthorCollection
    {
        if (count($authorUsernames) === 0) {
            $authorUsernames = $this->defaultAuthors;
        }

        $authorCollection = new AuthorCollection();

        foreach ($authorUsernames as $username) {
            $author = $this->authorRepository->findByAttributes(['username' => $username]);

            if ($author === null) {
                throw new AuthorNotFoundException("Unable to find author '$username'.");
            }

            $authorCollection[] = $author;
        }

        return $authorCollection;
    }
}
