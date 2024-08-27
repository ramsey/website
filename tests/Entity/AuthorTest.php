<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Author;
use App\Entity\AuthorLink;
use App\Entity\AuthorLinkType;
use App\Entity\Post;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Faker\Factory;
use Faker\Generator;
use InvalidArgumentException;
use Laminas\Diactoros\Uri;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
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

    #[TestDox('adds a link')]
    public function testAddLink(): void
    {
        $link = new AuthorLink();

        $author = new Author();

        $this->assertSame($author, $author->addLink($link));
        $this->assertTrue($author->getLinks()->contains($link));
        $this->assertSame($author, $link->getAuthor());
    }

    #[TestDox('throws an exception if the link belongs to another author')]
    public function testAddLinkThrowsExceptionWhenLinkAlreadyOwned(): void
    {
        $author1 = new Author();
        $author2 = new Author();

        $link = new AuthorLink();
        $link->setAuthor($author1);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('An author is already associated with this link');

        $author2->addLink($link);
    }

    #[TestDox('removes a link')]
    public function testRemoveLink(): void
    {
        $link1 = new AuthorLink();
        $link2 = new AuthorLink();
        $link3 = new AuthorLink();

        $author = new Author();

        $author->addLink($link1)->addLink($link2)->addLink($link3);

        // The author should contain all links, and each link should have the author.
        $this->assertTrue($author->getLinks()->contains($link1));
        $this->assertTrue($author->getLinks()->contains($link2));
        $this->assertTrue($author->getLinks()->contains($link3));
        $this->assertSame($author, $link1->getAuthor());
        $this->assertSame($author, $link2->getAuthor());
        $this->assertSame($author, $link3->getAuthor());

        // Remove the second link from the author.
        $this->assertSame($author, $author->removeLink($link2));

        // The author should still contain link 1 and link 1 should have the author.
        $this->assertTrue($author->getLinks()->contains($link1));
        $this->assertSame($author, $link1->getAuthor());

        // The author should no longer contain link 2 and link 2 should not have the author.
        $this->assertFalse($author->getLinks()->contains($link2));
        $this->assertNull($link2->getAuthor());

        // The author should still contain link 3 and link 3 should have the author.
        $this->assertTrue($author->getLinks()->contains($link3));
        $this->assertSame($author, $link3->getAuthor());
    }

    #[TestWith(['website'])]
    #[TestWith([AuthorLinkType::Website])]
    #[TestWith(['linkedin'])]
    #[TestWith([AuthorLinkType::LinkedIn])]
    #[TestWith(['mastodon'])]
    #[TestWith([AuthorLinkType::Mastodon])]
    #[TestWith([AuthorLinkType::GitHub])]
    #[TestWith([AuthorLinkType::SpeakerDeck])]
    public function testGetLink(AuthorLinkType | string $type): void
    {
        $uri1 = new Uri($this->faker->url());
        $link1 = (new AuthorLink())
            ->setType(AuthorLinkType::LinkedIn)
            ->setUrl($uri1);

        $uri2 = new Uri($this->faker->url());
        $link2 = (new AuthorLink())
            ->setType(AuthorLinkType::Website)
            ->setUrl($uri2);

        $uri3 = new Uri($this->faker->url());
        $link3 = (new AuthorLink())
            ->setType(AuthorLinkType::Mastodon)
            ->setUrl($uri3);

        $uri4 = new Uri($this->faker->url());
        $link4 = (new AuthorLink())
            ->setType(AuthorLinkType::Website)
            ->setUrl($uri4);

        $expected = match ($type) {
            'linkedin', AuthorLinkType::LinkedIn => $uri1,
            'website', AuthorLinkType::Website => $uri2,
            'mastodon', AuthorLinkType::Mastodon => $uri3,
            default => null,
        };

        $author = (new Author())
            ->addLink($link1)
            ->addLink($link2)
            ->addLink($link3)
            ->addLink($link4);

        $this->assertSame($expected, $author->getLink($type));
    }
}
