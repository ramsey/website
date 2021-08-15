<?php

declare(strict_types=1);

namespace AppTest\Handler;

use App\Handler\BlogHandler;
use App\Handler\BlogHandlerFactory;
use Ramsey\Test\Website\TestCase;

class BlogHandlerFactoryTest extends TestCase
{
    public function testInvoke(): void
    {
        $container = require __DIR__ . '/../../../config/container.php';

        $factory = new BlogHandlerFactory();
        $handler = $factory($container);

        $this->assertInstanceOf(BlogHandler::class, $handler);
    }
}
