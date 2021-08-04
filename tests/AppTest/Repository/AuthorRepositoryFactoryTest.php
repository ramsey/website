<?php

declare(strict_types=1);

namespace AppTest\Repository;

use App\Repository\AuthorRepository;
use App\Repository\AuthorRepositoryFactory;
use Ramsey\Test\Website\TestCase;

class AuthorRepositoryFactoryTest extends TestCase
{
    public function testInvoke(): void
    {
        $container = require __DIR__ . '/../../../config/container.php';

        $factory = new AuthorRepositoryFactory();
        $repository = $factory($container);

        $this->assertInstanceOf(AuthorRepository::class, $repository);
    }
}
