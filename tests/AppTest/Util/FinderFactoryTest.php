<?php

declare(strict_types=1);

namespace AppTest\Util;

use App\Util\FinderFactory;
use Ramsey\Test\Website\TestCase;
use Symfony\Component\Finder\Finder;

class FinderFactoryTest extends TestCase
{
    public function testInvoke(): void
    {
        $finderFactory = new FinderFactory();

        $this->assertInstanceOf(Finder::class, $finderFactory());
    }
}
