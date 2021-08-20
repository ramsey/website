<?php

declare(strict_types=1);

namespace AppTest\Handler\Blog;

use App\Handler\Blog\PostHandler;
use App\Handler\Blog\PostHandlerFactory;
use Ramsey\Test\Website\TestCase;

class PostHandlerFactoryTest extends TestCase
{
    public function testInvoke(): void
    {
        $container = require __DIR__ . '/../../../../config/container.php';

        $factory = new PostHandlerFactory();
        $handler = $factory($container);

        $this->assertInstanceOf(PostHandler::class, $handler);
    }
}
