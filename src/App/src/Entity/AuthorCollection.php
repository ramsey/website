<?php

declare(strict_types=1);

namespace App\Entity;

use Ramsey\Collection\AbstractCollection;
use Ramsey\Collection\CollectionInterface;

/**
 * A collection of Authors
 *
 * @extends AbstractCollection<Author>
 * @implements CollectionInterface<Author>
 */
class AuthorCollection extends AbstractCollection implements CollectionInterface
{
    public function getType(): string
    {
        return Author::class;
    }
}
