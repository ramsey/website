<?php

declare(strict_types=1);

namespace App\Tests\Command\Author;

use App\Entity\Author;
use App\Repository\AuthorRepository;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;

use function assert;
use function sprintf;

#[Group('commands')]
#[Group('db')]
#[TestDox('Command app:author:add-link')]
class AddLinkCommandTest extends KernelTestCase
{
    private CommandTester $commandTester;
    private Generator $faker;
    private AuthorRepository $repository;

    protected function setUp(): void
    {
        $this->faker = Factory::create();

        $kernel = self::bootKernel();
        assert(self::$kernel instanceof KernelInterface);
        $app = new Application(self::$kernel);

        $command = $app->find('app:author:add-link');
        $this->commandTester = new CommandTester($command);

        /** @var Registry $doctrine */
        $doctrine = $kernel->getContainer()->get('doctrine');
        $entityManager = $doctrine->getManager();

        $this->repository = $entityManager->getRepository(Author::class);
    }

    #[TestDox('adds a link to an author')]
    public function testAddAuthorLink(): void
    {
        $url = $this->faker->url();

        $this->commandTester->execute([
            'email' => 'author1@example.com',
            'type' => 'mastodon',
            'link' => $url,
        ]);

        $output = $this->commandTester->getDisplay();

        $this->commandTester->assertCommandIsSuccessful();
        $this->assertStringContainsString(sprintf('[INFO] Link "%s" added for author', $url), $output);

        $author = $this->repository->findOneBy(['email' => 'author1@example.com']);

        $hasLink = false;
        foreach ($author?->getLinks() ?? [] as $link) {
            if ((string) $link->getUrl() === $url) {
                $hasLink = true;
            }
        }

        $this->assertTrue($hasLink);
    }

    #[TestDox('prints error when author does not exist')]
    public function testWhenAuthorNotFound(): void
    {
        $url = $this->faker->url();

        $this->commandTester->execute([
            'email' => 'unknown-author@example.com',
            'type' => 'mastodon',
            'link' => $url,
        ]);

        $output = $this->commandTester->getDisplay();

        $this->assertSame(Command::FAILURE, $this->commandTester->getStatusCode());
        $this->assertStringContainsString(
            '[ERROR] The author for email "unknown-author@example.com" does not exist',
            $output,
        );
    }

    #[TestDox('prints error when type does not exist')]
    public function testWhenTypeNotFound(): void
    {
        $url = $this->faker->url();

        $this->commandTester->execute([
            'email' => 'author1@example.com',
            'type' => 'not-a-valid-type',
            'link' => $url,
        ]);

        $output = $this->commandTester->getDisplay();

        $this->assertSame(Command::FAILURE, $this->commandTester->getStatusCode());
        $this->assertStringContainsString('[ERROR] "not-a-valid-type" is not a valid backing value', $output);
    }

    #[TestDox('prints error when URL is empty string')]
    public function testWhenUrlIsEmpty(): void
    {
        $this->commandTester->execute([
            'email' => 'author1@example.com',
            'type' => 'website',
            'link' => '',
        ]);

        $output = $this->commandTester->getDisplay();

        $this->assertSame(Command::FAILURE, $this->commandTester->getStatusCode());
        $this->assertStringContainsString('[ERROR] The link cannot be an empty string.', $output);
    }

    #[TestDox('prints error when URL is invalid')]
    public function testWhenUrlIsInvalid(): void
    {
        $this->commandTester->execute([
            'email' => 'author1@example.com',
            'type' => 'website',
            'link' => 'bad-scheme://bad-uri',
        ]);

        $output = $this->commandTester->getDisplay();

        $this->assertSame(Command::FAILURE, $this->commandTester->getStatusCode());
        $this->assertStringContainsString('[ERROR] Unsupported scheme "bad-scheme"', $output);
    }
}
