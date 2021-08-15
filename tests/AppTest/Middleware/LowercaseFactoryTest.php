<?php

declare(strict_types=1);

namespace AppTest\Middleware;

use App\Middleware\LowercaseFactory;
use Middlewares\Lowercase;
use Ramsey\Test\Website\TestCase;

class LowercaseFactoryTest extends TestCase
{
    public function testInvoke(): void
    {
        $factory = new LowercaseFactory();
        $middleware = $factory();

        $this->assertInstanceOf(Lowercase::class, $middleware);
    }
}
