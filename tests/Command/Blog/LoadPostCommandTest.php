<?php

declare(strict_types=1);

namespace App\Tests\Command\Blog;

use App\Entity\Author;
use App\Entity\Post;
use App\Entity\PostBodyType;
use App\Entity\PostCategory;
use App\Entity\PostStatus;
use App\Entity\PostTag;
use App\Repository\PostRepository;
use Doctrine\Bundle\DoctrineBundle\Registry;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;

use function assert;

#[Group('commands')]
#[Group('db')]
#[TestDox('Command app:blog:load-post')]
class LoadPostCommandTest extends KernelTestCase
{
    private CommandTester $commandTester;
    private PostRepository $repository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        assert(self::$kernel instanceof KernelInterface);
        $app = new Application(self::$kernel);

        $command = $app->find('app:blog:load-post');
        $this->commandTester = new CommandTester($command);

        /** @var Registry $doctrine */
        $doctrine = $kernel->getContainer()->get('doctrine');
        $entityManager = $doctrine->getManager();

        $this->repository = $entityManager->getRepository(Post::class);
    }

    #[TestDox('displays an error when the file cannot be found')]
    public function testPathNotFound(): void
    {
        $this->commandTester->execute(['path' => 'path-to-nonexistent-file']);

        $output = $this->commandTester->getDisplay();

        $this->assertSame(Command::FAILURE, $this->commandTester->getStatusCode());
        $this->assertStringContainsString('Could not find file path-to-nonexistent-file', $output);
    }

    #[TestDox('loads a new static blog post and saves it to the database')]
    public function testCreateBlogPost(): void
    {
        $this->commandTester->execute(['path' => __DIR__ . '/fixtures/blog-post.md']);
        $output = $this->commandTester->getDisplay();

        $this->commandTester->assertCommandIsSuccessful();

        $this->assertStringContainsString(
            'Created blog post with ID 01913efd-d808-726a-b62d-20c5c34ca3bd',
            $output,
        );

        $post = $this->repository->find('01913efd-d808-726a-b62d-20c5c34ca3bd');

        $this->assertInstanceOf(Post::class, $post);
        $this->assertSame('01913efd-d808-726a-b62d-20c5c34ca3bd', $post->getId()->toString());
        $this->assertSame('Lorem Ipsum Odor Amet', $post->getTitle());
        $this->assertSame('2024-08-11 01:11:49', $post->getCreatedAt()->format('Y-m-d H:i:s'));
        $this->assertSame(PostStatus::Draft, $post->getStatus());
        $this->assertSame([PostCategory::Blog], $post->getCategory());
        $this->assertSame('lorem-ipsum', $post->getSlug());
        $this->assertSame('urn:uuid:01913efd-d808-726a-b62d-20c5c34ca3bd', $post->getFeedId());
        $this->assertSame('Lorem ipsum odor amet, consectetuer adipiscing elit.', $post->getDescription());
        $this->assertSame(['lorem', 'ipsum', 'odor', 'amet'], $post->getKeywords());
        $this->assertSame(
            'Lorem ipsum odor amet, consectetuer adipiscing elit. Conubia morbi lobortis '
                . 'interdum odio inceptos mollis nostra elementum. Finibus lobortis rhoncus '
                . 'faucibus leo enim.',
            $post->getExcerpt(),
        );
        $this->assertSame(PostBodyType::Markdown, $post->getBodyType());
        $this->assertStringContainsString(
            'Malesuada enim litora suspendisse quam posuere aptent placerat platea.',
            $post->getBody(),
        );
        $this->assertCount(3, $post->getTags());
        $this->assertTrue($post->getTags()->exists(fn ($k, PostTag $p): bool => $p->getName() === 'cool-stuff'));
        $this->assertTrue($post->getTags()->exists(fn ($k, PostTag $p): bool => $p->getName() === 'latin'));
        $this->assertTrue($post->getTags()->exists(fn ($k, PostTag $p): bool => $p->getName() === 'fake'));
        $this->assertCount(2, $post->getAuthors());
        $this->assertTrue($post->getAuthors()->exists(
            fn ($k, Author $p): bool => $p->getByline() === 'Frodo Baggins' && $p->getEmail() === 'frodo@example.com',
        ));
        $this->assertTrue($post->getAuthors()->exists(
            fn ($k, Author $p): bool => $p->getByline() === 'Samwise Gamgee' && $p->getEmail() === 'sam@example.com',
        ));
        $this->assertSame(
            ['layout' => 'post', 'shorturl' => 'https://bram.se/custom1'],
            $post->getMetadata(),
        );
        $this->assertCount(1, $post->getShortUrls());
        $this->assertSame('custom1', $post->getShortUrls()[0]?->getCustomSlug());
        $this->assertNull($post->getUpdatedAt());
    }

    #[TestDox('loads an existing static blog post and saves it to the database')]
    public function testUpdateBlogPost(): void
    {
        $this->commandTester->setInputs(['yes']);
        $this->commandTester->execute(['path' => __DIR__ . '/fixtures/blog-post-update.md']);
        $output = $this->commandTester->getDisplay();

        $this->commandTester->assertCommandIsSuccessful();

        $this->assertStringContainsString(
            'A post with ID 01913f38-fe0b-7220-bc2a-bea9e990d181 already exists. Do you want to update it?',
            $output,
        );

        $this->assertStringContainsString(
            'Updated blog post with ID 01913f38-fe0b-7220-bc2a-bea9e990d181',
            $output,
        );

        $post = $this->repository->find('01913f38-fe0b-7220-bc2a-bea9e990d181');

        $this->assertInstanceOf(Post::class, $post);
        $this->assertSame('01913f38-fe0b-7220-bc2a-bea9e990d181', $post->getId()->toString());
        $this->assertSame("Let's Update A Blog Post!", $post->getTitle());
        $this->assertSame('2024-08-08 13:32:45', $post->getCreatedAt()->format('Y-m-d H:i:s'));
        $this->assertSame(PostStatus::Hidden, $post->getStatus());
        $this->assertSame([PostCategory::Blog], $post->getCategory());
        $this->assertSame('a-beautiful-day-in-the-neighborhood', $post->getSlug());
        $this->assertNull($post->getFeedId());
        $this->assertNull($post->getDescription());
        $this->assertSame([], $post->getKeywords());
        $this->assertNull($post->getExcerpt());
        $this->assertSame(PostBodyType::Markdown, $post->getBodyType());
        $this->assertStringContainsString('Inceptos eget cursus aliquam primis suscipit nulla.', $post->getBody());
        $this->assertCount(1, $post->getTags());
        $this->assertSame('fun-things', $post->getTags()[0]?->getName());
        $this->assertCount(1, $post->getAuthors());
        $this->assertSame('author2@example.com', $post->getAuthors()[0]?->getEmail());
        $this->assertSame([], $post->getMetadata());
        $this->assertCount(1, $post->getShortUrls());
        $this->assertSame('2024-08-11 00:18:26', $post->getUpdatedAt()?->format('Y-m-d H:i:s'));
    }

    #[TestDox('aborts when confirmation question is answered in the negative')]
    public function testAbortUpdatingBlogPost(): void
    {
        $this->commandTester->setInputs(['no']);
        $this->commandTester->execute(['path' => __DIR__ . '/fixtures/blog-post-update.md']);
        $output = $this->commandTester->getDisplay();

        $this->assertSame(Command::FAILURE, $this->commandTester->getStatusCode());

        $this->assertStringContainsString(
            'A post with ID 01913f38-fe0b-7220-bc2a-bea9e990d181 already exists. Do you want to update it?',
            $output,
        );

        $this->assertStringContainsString('Aborting...', $output);
    }
}
