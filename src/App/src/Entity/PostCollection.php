<?php

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
