<?php

declare(strict_types=1);

namespace AppTest\Repository;

use App\Repository\PageRepository;
use App\Repository\PageRepositoryFactory;
use Ramsey\Test\Website\TestCase;

class PageRepositoryFactoryTest extends TestCase
{
    public function testInvoke(): void
    {
        $container = require __DIR__ . '/../../../config/container.php';

        $factory = new PageRepositoryFactory();
        $repository = $factory($container);

        $this->assertInstanceOf(PageRepository::class, $repository);
    }
}
