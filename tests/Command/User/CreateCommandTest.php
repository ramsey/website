<?php

declare(strict_types=1);

namespace App\Tests\Command\User;

use App\Entity\User;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;

use function assert;

#[Group('commands')]
#[Group('db')]
#[TestDox('Command app:user:create')]
final class CreateCommandTest extends KernelTestCase
{
    private CommandTester $commandTester;
    private Generator $faker;
    private UserRepository $repository;

    protected function setUp(): void
    {
        $this->faker = Factory::create();

        $kernel = self::bootKernel();
        assert(self::$kernel instanceof KernelInterface);
        $app = new Application(self::$kernel);

        $command = $app->find('app:user:create');
        $this->commandTester = new CommandTester($command);

        /** @var Registry $doctrine */
        $doctrine = $kernel->getContainer()->get('doctrine');
        $entityManager = $doctrine->getManager();

        $this->repository = $entityManager->getRepository(User::class);
    }

    #[TestDox('creates a user with no roles')]
    public function testExecute(): void
    {
        $name = $this->faker->name();
        $email = $this->faker->safeEmail();
        $password = $this->faker->password();

        $this->commandTester->setInputs([$password]);
        $this->commandTester->execute([
            'name' => $name,
            'email' => $email,
        ]);

        $this->commandTester->assertCommandIsSuccessful();

        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString("Created user $name with ID", $output);

        $user = $this->repository->findOneBy(['email' => $email]);

        $this->assertNotNull($user);
        $this->assertSame($name, $user->getName());
        $this->assertSame($email, $user->getEmail());
        $this->assertNotSame($password, $user->getPassword());
        $this->assertSame(['ROLE_USER'], $user->getRoles());
        $this->assertInstanceOf(DateTimeImmutable::class, $user->getCreatedAt());
        $this->assertNull($user->getUpdatedAt());
    }

    #[TestDox('creates a user with the given roles')]
    public function testExecuteWithRoles(): void
    {
        $name = $this->faker->name();
        $email = $this->faker->safeEmail();
        $password = $this->faker->password();

        $this->commandTester->setInputs([$password]);
        $this->commandTester->execute([
            '--role' => ['ROLE_ADMIN'],
            'name' => $name,
            'email' => $email,
        ]);

        $this->commandTester->assertCommandIsSuccessful();

        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString("Created user $name with ID", $output);

        $user = $this->repository->findOneBy(['email' => $email]);

        $this->assertNotNull($user);
        $this->assertSame(['ROLE_ADMIN', 'ROLE_USER'], $user->getRoles());
    }
}
