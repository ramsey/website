<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Author;
use App\Entity\Post;
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

#[TestDox('Author entity')]
class AuthorTest extends KernelTestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        $this->faker = Factory::create();
    }

    #[Group('db')]
    #[TestDox('is fully populated when retrieved from the database')]
    public function testEntityFromDatabase(): void
    {
        $kernel = self::bootKernel();

        /** @var Registry $doctrine */
        $doctrine = $kernel->getContainer()->get('doctrine');
        $entityManager = $doctrine->getManager();

        $repository = $entityManager->getRepository(Author::class);

        $author = $repository->findOneBy(['email' => 'author1@example.com']);
        assert($author instanceof Author);

        $this->assertInstanceOf(UuidInterface::class, $author->getId());
        $this->assertIsString($author->getByline());
        $this->assertGreaterThan(0, strlen($author->getByline()));
        $this->assertSame('author1@example.com', $author->getEmail());
        $this->assertInstanceOf(User::class, $author->getUser());
        $this->assertInstanceOf(DateTimeImmutable::class, $author->getCreatedAt());
        $this->assertNull($author->getUpdatedAt());
    }

    #[TestDox('sets the byline property')]
    public function testSetByline(): void
    {
        $name = $this->faker->name();

        $author = new Author();

        $this->assertSame($author, $author->setByline($name));
        $this->assertSame($name, $author->getByline());
    }

    #[TestDox('sets the email property')]
    public function testSetEmail(): void
    {
        $email = $this->faker->safeEmail();

        $author = new Author();

        $this->assertSame($author, $author->setEmail($email));
        $this->assertSame($email, $author->getEmail());
    }

    #[TestDox('sets the user property')]
    public function testSetUser(): void
    {
        $user = new User();

        $author = new Author();

        // Should allow returning null for the user.
        $this->assertNull($author->getUser());

        $this->assertSame($author, $author->setUser($user));
        $this->assertSame($user, $author->getUser());
    }

    #[TestDox('add a post')]
    public function testAddPost(): void
    {
        $post = new Post();

        $author = new Author();

        $this->assertSame($author, $author->addPost($post));
        $this->assertTrue($author->getPosts()->contains($post));
        $this->assertTrue($post->getAuthors()->contains($author));
    }

    #[TestDox('removes a post')]
    public function testRemovePost(): void
    {
        $post1 = new Post();
        $post2 = new Post();
        $post3 = new Post();

        $author = new Author();

        $author->addPost($post1)->addPost($post2)->addPost($post3);

        // The author should contain all posts, and each post should contain the author.
        $this->assertTrue($author->getPosts()->contains($post1));
        $this->assertTrue($post1->getAuthors()->contains($author));
        $this->assertTrue($author->getPosts()->contains($post2));
        $this->assertTrue($post2->getAuthors()->contains($author));
        $this->assertTrue($author->getPosts()->contains($post3));
        $this->assertTrue($post3->getAuthors()->contains($author));

        // Remove the second post from the author.
        $this->assertSame($author, $author->removePost($post2));

        // The author should still contain post 1 and post 1 should contain the author.
        $this->assertTrue($author->getPosts()->contains($post1));
        $this->assertTrue($post1->getAuthors()->contains($author));

        // The author should no longer contain post 2 and post 2 should not contain the author.
        $this->assertFalse($author->getPosts()->contains($post2));
        $this->assertFalse($post2->getAuthors()->contains($author));

        // The author should still contain post 3 and post 3 should contain the author.
        $this->assertTrue($author->getPosts()->contains($post3));
        $this->assertTrue($post3->getAuthors()->contains($author));
    }
}
