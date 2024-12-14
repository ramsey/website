<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestDox;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

use function assert;
use function strlen;

#[TestDox('User entity')]
class UserTest extends KernelTestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        $this->faker = Factory::create();
    }

    #[Group('db')]
    #[TestDox('is fully populated when retrieved from the database')]
    public function testUserEntityFromDatabase(): void
    {
        $kernel = self::bootKernel();

        /** @var Registry $doctrine */
        $doctrine = $kernel->getContainer()->get('doctrine');
        $entityManager = $doctrine->getManager();

        $repository = $entityManager->getRepository(User::class);

        $user = $repository->findOneBy(['email' => 'admin-user@example.com']);
        assert($user instanceof User);

        $this->assertInstanceOf(UuidInterface::class, $user->getId());
        $this->assertIsString($user->getName());
        $this->assertGreaterThan(0, strlen($user->getName()));
        $this->assertIsString($user->getEmail());
        $this->assertGreaterThan(0, strlen($user->getEmail()));
        $this->assertSame($user->getEmail(), $user->getUserIdentifier());
        $this->assertSame(['ROLE_USER', 'ROLE_ADMIN'], $user->getRoles());
        $this->assertIsString($user->getPassword());
        $this->assertGreaterThan(0, strlen($user->getPassword()));
        $this->assertInstanceOf(DateTimeImmutable::class, $user->getCreatedAt());
        $this->assertInstanceOf(DateTimeImmutable::class, $user->getUpdatedAt());
    }

    #[TestDox('sets the name property')]
    public function testSetName(): void
    {
        $name = $this->faker->name();

        $user = new User();

        $this->assertSame($user, $user->setName($name));
        $this->assertSame($name, $user->getName());
    }

    #[TestDox('sets the email property')]
    public function testSetEmail(): void
    {
        $email = $this->faker->safeEmail();
        assert(strlen($email) > 0);

        $user = new User();

        $this->assertSame($user, $user->setEmail($email));
        $this->assertSame($email, $user->getEmail());
    }

    #[TestDox('sets the roles property')]
    public function testSetRoles(): void
    {
        $user = new User();

        $this->assertSame($user, $user->setRoles(['ROLE_SUPER_ADMIN', 'ROLE_ADMIN']));
        $this->assertSame(['ROLE_SUPER_ADMIN', 'ROLE_ADMIN', 'ROLE_USER'], $user->getRoles());
    }

    #[TestDox('sets the password property')]
    public function testSetPassword(): void
    {
        $password = $this->faker->password();

        $user = new User();

        $this->assertSame($user, $user->setPassword($password));
        $this->assertSame($password, $user->getPassword());
    }
}
