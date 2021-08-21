<?php

declare(strict_types=1);

namespace AppTest\Handler\Blog;

use App\Handler\Blog\FeedHandler;
use App\Handler\Blog\FeedHandlerFactory;
use Ramsey\Test\Website\TestCase;

class FeedHandlerFactoryTest extends TestCase
{
    public function testInvoke(): void
    {
        $container = require __DIR__ . '/../../../../config/container.php';

        $factory = new FeedHandlerFactory();
        $handler = $factory($container);

        $this->assertInstanceOf(FeedHandler::class, $handler);
    }
}
