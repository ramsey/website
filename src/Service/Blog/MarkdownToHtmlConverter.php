<?php

/**
 * This file is part of ramsey/website
 *
 * Copyright (c) Ben Ramsey <ben@ramsey.dev>
 *
 * ramsey/website is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Affero General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.
 *
 * ramsey/website is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with ramsey/website. If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace App\Service\Blog;

use App\Entity\Post;
use App\Entity\PostBodyType;
use League\CommonMark\ConverterInterface;

use function sprintf;

/**
 * A post body converter that converts Markdown posts to HTML
 */
final readonly class MarkdownToHtmlConverter implements PostBodyConverter
{
    public function __construct(private ConverterInterface $converter)
    {
    }

    public function convert(Post $post): string
    {
        if ($post->getBodyType() === PostBodyType::Markdown) {
            return $this->converter->convert($post->getBody())->getContent();
        }

        throw new UnsupportedPostBodyType(sprintf(
            '%s does not support %s',
            $this::class,
            $post->getBodyType()->value,
        ));
    }
}
