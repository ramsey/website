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

namespace App\Entity;

use DateTimeInterface;

/**
 * Website posts are pages posted at specific times with authors
 */
class Post extends Page implements Attributable
{
    public function __construct(
        string $title,
        string $content,
        private DateTimeInterface $publishDate,
        private ?AuthorCollection $authors = null,
        ?Attributes $attributes = null,
        private ?DateTimeInterface $lastUpdateDate = null,
    ) {
        parent::__construct($title, $content, $attributes);
    }

    public function getPublishDate(): DateTimeInterface
    {
        return $this->publishDate;
    }

    public function getAuthors(): ?AuthorCollection
    {
        return $this->authors;
    }

    public function getLastUpdateDate(): ?DateTimeInterface
    {
        return $this->lastUpdateDate;
    }
}
