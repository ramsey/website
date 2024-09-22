<?php

declare(strict_types=1);

namespace App\Tests\Command\Blog;

use App\Entity\Post;
use App\Repository\PostRepository;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Persistence\ObjectManager;
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
#[TestDox('Command app:blog:load-posts')]
class LoadPostsCommandTest extends KernelTestCase
{
    private CommandTester $commandTester;
    private PostRepository $repository;
    private ObjectManager $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        assert(self::$kernel instanceof KernelInterface);
        $app = new Application(self::$kernel);

        $command = $app->find('app:blog:load-posts');
        $this->commandTester = new CommandTester($command);

        /** @var Registry $doctrine */
        $doctrine = $kernel->getContainer()->get('doctrine');
        $this->entityManager = $doctrine->getManager();

        $this->repository = $this->entityManager->getRepository(Post::class);
    }

    #[TestDox('displays an error when the directory cannot be found')]
    public function testDirectoryNotFound(): void
    {
        $this->commandTester->execute(['path' => 'path-to-nonexistent-directory']);

        $output = $this->commandTester->getDisplay();

        $this->assertSame(Command::FAILURE, $this->commandTester->getStatusCode());
        $this->assertStringContainsString(
            '[ERROR] The "path-to-nonexistent-directory" directory does not exist.',
            $output,
        );
    }

    #[TestDox('displays a message when no blog posts found in directory')]
    public function testNoBlogPostsNotFoundInDirectory(): void
    {
        $this->commandTester->execute(['path' => __DIR__ . '/fixtures/directory-with-no-posts']);

        $output = $this->commandTester->getDisplay();

        $this->commandTester->assertCommandIsSuccessful();

        $this->assertStringContainsString('No posts found in', $output);
    }

    #[TestDox('displays an error when encountering a blog post with an error')]
    public function testBlogPostWithError(): void
    {
        $this->commandTester->execute(['path' => __DIR__ . '/fixtures/error-posts']);

        $output = $this->commandTester->getDisplay();

        $this->assertSame(Command::FAILURE, $this->commandTester->getStatusCode());
        $this->assertStringContainsString('[ERROR] An error occurred while loading blog posts.', $output);
        $this->assertStringContainsString(
            '[ERROR] Short URL https://bram.se/slug-does-not-exist-so-this-should-error does not exist',
            $output,
        );
    }

    #[TestDox('loads blog posts from directory and saves them to the database')]
    public function testCreateBlogPost(): void
    {
        $this->commandTester->execute(['path' => __DIR__ . '/fixtures/good-posts']);
        $output = $this->commandTester->getDisplay();

        $this->commandTester->assertCommandIsSuccessful();

        $this->assertStringNotContainsString('[DRY-RUN]', $output);
        $this->assertStringNotContainsString('already exists. Do you want to update it?', $output);
        $this->assertStringContainsString('Saved blog post for 2024-08-11: "Lorem Ipsum Odor Amet"', $output);
        $this->assertStringContainsString('Saved blog post for 2024-08-08: "Let\'s Update A Blog Post!"', $output);

        // Clear the entity manager so that the next find() calls make
        // fresh database requests for the entities.
        $this->entityManager->clear();

        $post1 = $this->repository->find('01913efd-d808-726a-b62d-20c5c34ca3bd');
        $post2 = $this->repository->find('01913f38-fe0b-7220-bc2a-bea9e990d181');

        $this->assertInstanceOf(Post::class, $post1);
        $this->assertNull($post1->getUpdatedAt());

        $this->assertInstanceOf(Post::class, $post2);
        $this->assertNotNull($post2->getUpdatedAt());
    }
}
