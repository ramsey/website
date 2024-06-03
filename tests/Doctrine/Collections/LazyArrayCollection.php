<?php

declare(strict_types=1);

namespace App\Tests\Doctrine\Collections;

use Doctrine\Common\Collections\AbstractLazyCollection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\ReadableCollection;
use Doctrine\Common\Collections\Selectable;

/**
 * @template TKey of array-key
 * @template T
 * @extends AbstractLazyCollection<TKey,T>
 * @implements ReadableCollection<TKey,T>
 * @implements Selectable<TKey,T>
 */
final class LazyArrayCollection extends AbstractLazyCollection implements ReadableCollection, Selectable
{
    /**
     * @param array<TKey, T> $elements
     */
    public function __construct(array $elements = [])
    {
        $this->collection = new ArrayCollection($elements);
    }

    protected function doInitialize(): void
    {
    }

    /**
     * @return self<TKey, T>
     */
    public function matching(Criteria $criteria): self
    {
        return $this;
    }
}
