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
use App\Entity\Author;
use App\Repository\Exception\MultipleMatches;
use App\Util\FinderFactory;
use Psr\Http\Message\UriFactoryInterface;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Parser;

use function sprintf;

class AuthorRepository
{
    private const FILENAME_PATTERN = '/^%s\.(yaml|yml)$/';

    public function __construct(
        private FinderFactory $finderFactory,
        private string $authorsPath,
        private Parser $yamlParser,
        private UriFactoryInterface $uriFactory,
    ) {
    }

    /**
     * @param array{username?: string} $attributes
     */
    public function findByAttributes(array $attributes): ?Author
    {
        if (isset($attributes['username'])) {
            return $this->findByUsername($attributes['username']);
        }

        return null;
    }

    private function findByUsername(string $authorUsername): ?Author
    {
        $files = ($this->finderFactory)()
            ->files()
            ->in($this->authorsPath)
            ->name(sprintf(self::FILENAME_PATTERN, $authorUsername));

        if ($files->count() > 1) {
            throw new MultipleMatches(sprintf(
                'More than one author matches %s',
                $authorUsername,
            ));
        }

        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            return $this->convertToAuthor($file);
        }

        return null;
    }

    private function convertToAuthor(SplFileInfo $file): Author
    {
        /** @var array<string, mixed> $yaml */
        $yaml = $this->yamlParser->parse($file->getContents());

        /** @var string $name */
        $name = $yaml['name'] ?? $file->getFilenameWithoutExtension();

        /** @var string|null $biography */
        $biography = $yaml['biography'] ?? null;

        /** @var string|null $url */
        $url = $yaml['url'] ?? null;

        /** @var string|null $imageUrl */
        $imageUrl = $yaml['imageUrl'] ?? null;

        /** @var string|null $email */
        $email = $yaml['email'] ?? null;

        unset($yaml['name'], $yaml['biography'], $yaml['url'], $yaml['imageUrl'], $yaml['email']);

        $uriInstance = $url ? $this->uriFactory->createUri($url) : null;
        $imageUriInstance = $imageUrl ? $this->uriFactory->createUri($imageUrl) : null;
        $attributes = new Attributes($yaml);

        return new Author(
            name: $name,
            biography: $biography,
            url: $uriInstance,
            imageUrl: $imageUriInstance,
            email: $email,
            attributes: $attributes,
        );
    }
}
