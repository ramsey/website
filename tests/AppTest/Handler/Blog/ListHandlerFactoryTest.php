<?php

declare(strict_types=1);

namespace AppTest\Handler\Blog;

use App\Handler\Blog\ListHandler;
use App\Handler\Blog\ListHandlerFactory;
use Ramsey\Test\Website\TestCase;

class ListHandlerFactoryTest extends TestCase
{
    public function testInvoke(): void
    {
        $container = require __DIR__ . '/../../../../config/container.php';

        $factory = new ListHandlerFactory();
        $handler = $factory($container);

        $this->assertInstanceOf(ListHandler::class, $handler);
    }
}
