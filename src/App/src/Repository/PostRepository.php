<?php

/**
 * This file is part of ramsey/website
 *
 * ramsey/website is open source software: you can distribute
 * it and/or modify it under the terms of the MIT License
 * (the "License"). You may not use this file except in
 * compliance with the License.
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or
 * implied. See the License for the specific language governing
 * permissions and limitations under the License.
 *
 * @copyright Copyright (c) Ben Ramsey <ben@benramsey.com>
 * @license https://opensource.org/licenses/MIT MIT License
 */

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Attributes;
use App\Entity\AuthorCollection;
use App\Entity\Post;
use App\Repository\Exception\AuthorNotFound;
use App\Repository\Exception\MultipleMatches;
use App\Util\FinderFactory;
use DateTimeImmutable;
use League\CommonMark\Extension\FrontMatter\Output\RenderedContentWithFrontMatter;
use League\CommonMark\MarkdownConverterInterface;
use Symfony\Component\Finder\SplFileInfo;

use function count;
use function preg_match;
use function sprintf;

class PostRepository
{
    private const FILE_DATE_PATTERN = '/.*(\d{4}-\d{2}-\d{2}).*/';

    /**
     * @param string[] $defaultAuthors
     */
    public function __construct(
        private FinderFactory $finderFactory,
        private string $postsPath,
        private MarkdownConverterInterface $markdownConverter,
        private AuthorRepository $authorRepository,
        private array $defaultAuthors = [],
    ) {
    }

    /**
     * @param array{year?: int, month?: int, slug?: string} $attributes
     */
    public function findByAttributes(array $attributes): ?Post
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

    private function findByYearMonthSlug(int $year, int $month, string $slug): ?Post
    {
        $files = ($this->finderFactory)()
            ->files()
            ->in($this->postsPath)
            ->name(sprintf("/^%d-%'.02d-\d{2}-%s\.(md|html|markdown)$/", $year, $month, $slug));

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

    private function findByYearSlug(int $year, string $slug): ?Post
    {
        $files = ($this->finderFactory)()
            ->files()
            ->in($this->postsPath)
            ->name(sprintf('/^%d-\d{2}-\d{2}-%s\.(md|html|markdown)$/', $year, $slug));

        if ($files->count() > 1) {
            throw new MultipleMatches(sprintf(
                'More than one post matches for year: %d, slug: %s',
                $year,
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

        if ($publishDate === '' && preg_match(self::FILE_DATE_PATTERN, $file->getFilename(), $matches)) {
            $publishDate = $matches[1] ?? '';
        }

        /** @var string $title */
        $title = $frontMatter['title'] ?? 'Untitled';

        /** @var string[] $authorUsernames */
        $authorUsernames = $frontMatter['authors'] ?? [];

        return new Post(
            title: $title,
            content: $markdownContents->getContent(),
            publishDate: new DateTimeImmutable($publishDate),
            authors: $this->getAuthors($authorUsernames),
            attributes: $attributes,
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
                throw new AuthorNotFound("Unable to find author '$username'.");
            }

            $authorCollection[] = $author;
        }

        return $authorCollection;
    }
}
