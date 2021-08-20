<?php

declare(strict_types=1);

namespace AppTest\Handler;

use App\Handler\PageHandler;
use App\Handler\PageHandlerFactory;
use Ramsey\Test\Website\TestCase;

class PageHandlerFactoryTest extends TestCase
{
    public function testInvoke(): void
    {
        $container = require __DIR__ . '/../../../config/container.php';

        $factory = new PageHandlerFactory();
        $handler = $factory($container);

        $this->assertInstanceOf(PageHandler::class, $handler);
    }
}
