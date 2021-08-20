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
        $container = require __DIR__ . '/../../../config/container.php';

        $factory = new LowercaseFactory();
        $middleware = $factory($container);

        $this->assertInstanceOf(Lowercase::class, $middleware);
    }
}
