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

use Ramsey\Collection\AbstractCollection;
use Ramsey\Collection\CollectionInterface;

/**
 * A collection of Posts
 *
 * @extends AbstractCollection<Post>
 * @implements CollectionInterface<Post>
 */
class PostCollection extends AbstractCollection implements CollectionInterface
{
    public function getType(): string
    {
        return Post::class;
    }
}
