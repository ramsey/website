<?php

declare(strict_types=1);

namespace AppTest\Middleware;

use App\Middleware\TrailingSlashFactory;
use Middlewares\TrailingSlash;
use Ramsey\Test\Website\TestCase;

class TrailingSlashFactoryTest extends TestCase
{
    public function testInvoke(): void
    {
        $container = require __DIR__ . '/../../../config/container.php';

        $factory = new TrailingSlashFactory();
        $middleware = $factory($container);

        $this->assertInstanceOf(TrailingSlash::class, $middleware);
    }
}
