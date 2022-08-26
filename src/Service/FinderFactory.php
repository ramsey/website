<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Finder\Finder;

/**
 * A service to create new Finder instances
 */
class FinderFactory
{
    /**
     * Creates a new Symfony Finder component instance
     */
    public function createFinder(): Finder
    {
        return Finder::create();
    }
}
