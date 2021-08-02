<?php

declare(strict_types=1);

namespace AppTest\Repository;

use App\Repository\PostRepository;
use App\Repository\PostRepositoryFactory;
use Ramsey\Test\Website\TestCase;

class PostRepositoryFactoryTest extends TestCase
{
    public function testInvoke(): void
    {
        $container = require __DIR__ . '/../../../config/container.php';

        $factory = new PostRepositoryFactory();
        $repository = $factory($container);

        $this->assertInstanceOf(PostRepository::class, $repository);
    }
}
