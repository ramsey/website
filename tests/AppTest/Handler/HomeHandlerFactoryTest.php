<?php

declare(strict_types=1);

namespace AppTest\Handler;

use App\Handler\HomeHandler;
use App\Handler\HomeHandlerFactory;
use Ramsey\Test\Website\TestCase;

class HomeHandlerFactoryTest extends TestCase
{
    public function testInvoke(): void
    {
        $container = require __DIR__ . '/../../../config/container.php';

        $factory = new HomeHandlerFactory();
        $handler = $factory($container);

        $this->assertInstanceOf(HomeHandler::class, $handler);
    }
}
