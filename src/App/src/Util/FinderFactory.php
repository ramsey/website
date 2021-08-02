<?php

declare(strict_types=1);

namespace App\Util;

use Symfony\Component\Finder\Finder;

class FinderFactory
{
    public function __invoke(): Finder
    {
        return new Finder();
    }
}
