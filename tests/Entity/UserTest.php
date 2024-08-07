<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Post;
use App\Entity\User;
use DateTime;
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

    #[TestDox('sets the createdAt property')]
    public function testSetCreatedAt(): void
    {
        $date = new DateTime();

        $user = new User();

        $this->assertSame($user, $user->setCreatedAt($date));
        $this->assertNotSame($date, $user->getCreatedAt());
        $this->assertInstanceOf(DatetimeImmutable::class, $user->getCreatedAt());
        $this->assertSame($date->format('c'), $user->getCreatedAt()->format('c'));
    }

    #[TestDox('sets the updatedAt property')]
    public function testSetUpdatedAt(): void
    {
        $date = new DateTime();

        $user = new User();

        $this->assertSame($user, $user->setUpdatedAt($date));
        $this->assertNotSame($date, $user->getUpdatedAt());
        $this->assertInstanceOf(DatetimeImmutable::class, $user->getUpdatedAt());
        $this->assertSame($date->format('c'), $user->getUpdatedAt()->format('c'));
    }

    #[TestDox('sets the deletedAt property')]
    public function testSetDeletedAt(): void
    {
        $date = new DateTime();

        $user = new User();

        $this->assertSame($user, $user->setDeletedAt($date));
        $this->assertNotSame($date, $user->getDeletedAt());
        $this->assertInstanceOf(DatetimeImmutable::class, $user->getDeletedAt());
        $this->assertSame($date->format('c'), $user->getDeletedAt()->format('c'));
    }

    #[TestDox('adds and removes posts, which also adds/removes the authors to/from the posts')]
    public function testAssociatingPostsWithTags(): void
    {
        $post1 = new Post();
        $post2 = new Post();
        $post3 = new Post();
        $user = new User();

        $this->assertTrue($user->getPosts()->isEmpty());
        $this->assertSame($user, $user->addPost($post1));
        $this->assertSame($user, $user->addPost($post2));
        $this->assertSame($user, $user->addPost($post3));

        // Attempting to add the same post is a no-op.
        $this->assertSame($user, $user->addPost($post2));

        $this->assertCount(3, $user->getPosts());
        $this->assertTrue($user->getPosts()->contains($post1));
        $this->assertTrue($user->getPosts()->contains($post2));
        $this->assertTrue($user->getPosts()->contains($post3));

        // All posts should have the author added to them.
        $this->assertSame($user, $post1->getAuthor());
        $this->assertSame($user, $post2->getAuthor());
        $this->assertSame($user, $post3->getAuthor());

        // Attempt to remove a post.
        $this->assertSame($user, $user->removePost($post2));
        $this->assertCount(2, $user->getPosts());
        $this->assertTrue($user->getPosts()->contains($post1));
        $this->assertFalse($user->getPosts()->contains($post2));
        $this->assertTrue($user->getPosts()->contains($post3));
        $this->assertNull($post2->getAuthor());
    }
}
