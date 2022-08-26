<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\FinderFactory;
use App\Tests\TestCase;
use Symfony\Component\Finder\Finder;

class FinderFactoryTest extends TestCase
{
    public function testCreateFinder(): void
    {
        $finderFactory = new FinderFactory();
        $finder = $finderFactory->createFinder();

        $this->assertInstanceOf(Finder::class, $finder);
    }

    public function testCreateFinderCreatesNewInstances(): void
    {
        $finderFactory = new FinderFactory();
        $finder = $finderFactory->createFinder();

        $this->assertNotSame($finder, $finderFactory->createFinder());
    }
}
