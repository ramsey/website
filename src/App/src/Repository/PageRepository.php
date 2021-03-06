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
use App\Entity\Page;
use App\Repository\Exception\MultipleMatches;
use App\Util\FinderFactory;
use League\CommonMark\Extension\FrontMatter\Output\RenderedContentWithFrontMatter;
use League\CommonMark\MarkdownConverterInterface;
use Symfony\Component\Finder\SplFileInfo;

use function sprintf;
use function str_contains;
use function str_replace;
use function strrpos;
use function substr;
use function trim;

class PageRepository
{
    private const FILENAME_PATTERN = '/^%s\.(md|markdown|html)$/';

    public function __construct(
        private FinderFactory $finderFactory,
        private string $pagesPath,
        private MarkdownConverterInterface $markdownConverter,
    ) {
    }

    /**
     * @param array{slug?: string} $attributes
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
        $slug = trim($slug, '/');
        $pathPortion = '';

        if (str_contains($slug, '/')) {
            $lastSlash = strrpos($slug, '/') ?: null;
            $pathPortion = substr($slug, 0, $lastSlash);
            $slug = $lastSlash === null ? $slug : substr($slug, $lastSlash + 1);
        }

        $files = ($this->finderFactory)()
            ->files()
            ->in($this->pagesPath . ($pathPortion ? '/' . $pathPortion : ''))
            ->name(sprintf(self::FILENAME_PATTERN, str_replace('/', '\\/', $slug)));

        if ($files->count() > 1) {
            throw new MultipleMatches(sprintf('More than one page matches %s', $slug));
        }

        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            return $this->convertToPage($file);
        }

        return null;
    }

    private function convertToPage(SplFileInfo $file): Page
    {
        $markdownContents = $this->markdownConverter->convertToHtml($file->getContents());
        $frontMatter = [];

        if ($markdownContents instanceof RenderedContentWithFrontMatter) {
            /** @var array<string, mixed> $frontMatter */
            $frontMatter = $markdownContents->getFrontMatter();
        }

        $attributes = new Attributes($frontMatter);

        /** @var string $title */
        $title = $frontMatter['title'] ?? 'Untitled';

        return new Page(
            title: $title,
            content: $markdownContents->getContent(),
            attributes: $attributes,
        );
    }
}
