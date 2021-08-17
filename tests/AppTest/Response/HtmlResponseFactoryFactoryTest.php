<?php

declare(strict_types=1);

namespace AppTest\Response;

use App\Response\HtmlResponseFactory;
use App\Response\HtmlResponseFactoryFactory;
use Ramsey\Test\Website\TestCase;

class HtmlResponseFactoryFactoryTest extends TestCase
{
    public function testInvoke(): void
    {
        $container = require __DIR__ . '/../../../config/container.php';

        $factory = new HtmlResponseFactoryFactory();
        $responseFactory = $factory($container);

        $this->assertInstanceOf(HtmlResponseFactory::class, $responseFactory);
    }
}
