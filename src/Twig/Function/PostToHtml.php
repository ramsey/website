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

namespace App\Twig\Function;

use App\Entity\Post;
use App\Service\Blog\PostBodyConverter;
use Twig\Markup;
use Twig\TwigFunction;

/**
 * A Twig function that converts a Post body to HTML output
 */
final readonly class PostToHtml implements TwigFunctionFactory
{
    public function __construct(private PostBodyConverter $postBodyToHtmlConverter)
    {
    }

    public function __invoke(Post $post): Markup
    {
        return new Markup($this->postBodyToHtmlConverter->convert($post), 'utf-8');
    }

    public function getFunctionName(): string
    {
        return 'post_to_html';
    }

    public function getTwigFunction(): TwigFunction
    {
        return new TwigFunction($this->getFunctionName(), $this(...));
    }
}
