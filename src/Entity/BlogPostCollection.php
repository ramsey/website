<?php

declare(strict_types=1);

namespace App\Entity;

use Ramsey\Collection\AbstractCollection;
use Ramsey\Collection\CollectionInterface;

/**
 * A collection of blog posts
 *
 * @extends AbstractCollection<BlogPost>
 * @implements CollectionInterface<BlogPost>
 */
final class BlogPostCollection extends AbstractCollection implements CollectionInterface
{
    public function getType(): string
    {
        return BlogPost::class;
    }
}
