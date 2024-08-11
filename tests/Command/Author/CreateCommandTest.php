<?php

declare(strict_types=1);

namespace App\Tests\Command\Author;

use App\Entity\Author;
use App\Entity\User;
use App\Repository\AuthorRepository;
use App\Repository\UserRepository;
use DateTimeImmutable;
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
#[TestDox('Command app:author:create')]
class CreateCommandTest extends KernelTestCase
{
    private CommandTester $commandTester;
    private Generator $faker;
    private AuthorRepository $repository;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        $this->faker = Factory::create();

        $kernel = self::bootKernel();
        assert(self::$kernel instanceof KernelInterface);
        $app = new Application(self::$kernel);

        $command = $app->find('app:author:create');
        $this->commandTester = new CommandTester($command);

        /** @var Registry $doctrine */
        $doctrine = $kernel->getContainer()->get('doctrine');
        $entityManager = $doctrine->getManager();

        $this->repository = $entityManager->getRepository(Author::class);
        $this->userRepository = $entityManager->getRepository(User::class);
    }

    #[TestDox('creates a single author')]
    public function testCreateAnAuthor(): void
    {
        $byline = $this->faker->name();
        $email = $this->faker->safeEmail();

        $this->commandTester->execute([
            'authors' => "$byline <$email>",
        ]);

        $this->commandTester->assertCommandIsSuccessful();

        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString('Authors', $output);
        $this->assertStringContainsString($byline, $output);
        $this->assertStringContainsString($email, $output);

        $author = $this->repository->findOneBy(['email' => $email]);

        $this->assertNotNull($author);
        $this->assertSame($byline, $author->getByline());
        $this->assertSame($email, $author->getEmail());
        $this->assertNull($author->getUser());
    }

    #[TestDox('creates multiple authors')]
    public function testCreateAuthors(): void
    {
        $author1Byline = $this->faker->name();
        $author1Email = $this->faker->safeEmail();
        $author2Byline = $this->faker->name();
        $author2Email = $this->faker->safeEmail();
        $author3Byline = $this->faker->name();
        $author3Email = $this->faker->safeEmail();

        $this->commandTester->execute([
            'authors' =>
                "$author1Byline <$author1Email>, $author2Byline <$author2Email>, $author3Byline <$author3Email>",
        ]);

        $this->commandTester->assertCommandIsSuccessful();

        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString('Authors', $output);
        $this->assertStringContainsString($author1Byline, $output);
        $this->assertStringContainsString($author1Email, $output);
        $this->assertStringContainsString($author2Byline, $output);
        $this->assertStringContainsString($author2Email, $output);
        $this->assertStringContainsString($author3Byline, $output);
        $this->assertStringContainsString($author3Email, $output);

        $author1 = $this->repository->findOneBy(['email' => $author1Email]);
        $author2 = $this->repository->findOneBy(['email' => $author2Email]);
        $author3 = $this->repository->findOneBy(['email' => $author3Email]);

        $this->assertNotNull($author1);
        $this->assertSame($author1Byline, $author1->getByline());
        $this->assertSame($author1Email, $author1->getEmail());
        $this->assertNull($author1->getUser());

        $this->assertNotNull($author2);
        $this->assertSame($author2Byline, $author2->getByline());
        $this->assertSame($author2Email, $author2->getEmail());
        $this->assertNull($author2->getUser());

        $this->assertNotNull($author3);
        $this->assertSame($author3Byline, $author3->getByline());
        $this->assertSame($author3Email, $author3->getEmail());
        $this->assertNull($author3->getUser());
    }

    #[TestDox('creates authors tied to a user')]
    public function testCreateAuthorsWithUser(): void
    {
        $knownUser = $this->userRepository->findOneBy(['email' => 'user@example.com']);
        assert($knownUser instanceof User);

        $author1Byline = $this->faker->name();
        $author1Email = $this->faker->safeEmail();
        $author2Byline = $this->faker->name();
        $author2Email = $this->faker->safeEmail();

        $this->commandTester->execute([
            '--user-id' => $knownUser->getId(),
            'authors' => "$author1Byline <$author1Email>, $author2Byline <$author2Email>",
        ]);

        $this->commandTester->assertCommandIsSuccessful();

        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString('Authors', $output);
        $this->assertStringContainsString($author1Byline, $output);
        $this->assertStringContainsString($author1Email, $output);
        $this->assertStringContainsString($author2Byline, $output);
        $this->assertStringContainsString($author2Email, $output);

        $this->assertStringContainsString(
            sprintf(
                '[INFO] All authors are associated with user "%s" (%s).',
                $knownUser->getName(),
                $knownUser->getId(),
            ),
            $output,
        );

        $author1 = $this->repository->findOneBy(['email' => $author1Email]);
        $author2 = $this->repository->findOneBy(['email' => $author2Email]);

        $this->assertNotNull($author1);
        $this->assertSame($author1Byline, $author1->getByline());
        $this->assertSame($author1Email, $author1->getEmail());
        $this->assertSame($knownUser, $author1->getUser());

        $this->assertNotNull($author2);
        $this->assertSame($author2Byline, $author2->getByline());
        $this->assertSame($author2Email, $author2->getEmail());
        $this->assertSame($knownUser, $author2->getUser());
    }

    #[TestDox('confirms attempt to change an author byline')]
    public function testConfirmChangeByline(): void
    {
        $knownAuthor = $this->repository->findOneBy(['email' => 'author2@example.com']);
        assert($knownAuthor instanceof Author);

        $knownAuthorName = $knownAuthor->getByline();
        $knownAuthorEmail = $knownAuthor->getEmail();

        $author1Byline = $this->faker->name();
        $author1Email = $this->faker->safeEmail();
        $author2Byline = $this->faker->name();

        $this->commandTester->setInputs(['yes']);
        $this->commandTester->execute([
            'authors' => "$author1Byline <$author1Email>, $author2Byline <$knownAuthorEmail>",
        ]);

        $this->commandTester->assertCommandIsSuccessful();

        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString(
            sprintf(
                "Do you want to change the byline for <%s> to '%s' from '%s'",
                $knownAuthorEmail,
                $author2Byline,
                $knownAuthorName,
            ),
            $output,
        );

        $this->assertStringContainsString('Authors', $output);
        $this->assertStringContainsString($author1Byline, $output);
        $this->assertStringContainsString($author1Email, $output);
        $this->assertStringContainsString($author2Byline, $output);
        $this->assertStringContainsString($knownAuthorEmail, $output);

        $author1 = $this->repository->findOneBy(['email' => $author1Email]);
        $author2 = $this->repository->findOneBy(['email' => $knownAuthorEmail]);

        $this->assertNotNull($author1);
        $this->assertSame($author1Byline, $author1->getByline());
        $this->assertSame($author1Email, $author1->getEmail());
        $this->assertNull($author1->getUser());
        $this->assertNull($author1->getUpdatedAt());

        $this->assertNotNull($author2);
        $this->assertSame($author2Byline, $author2->getByline());
        $this->assertSame($knownAuthorEmail, $author2->getEmail());
        $this->assertNull($author2->getUser());
        $this->assertInstanceOf(DateTimeImmutable::class, $author2->getUpdatedAt());
    }

    #[TestDox('aborts attempt to change an author byline')]
    public function testAbortsChangeByline(): void
    {
        $knownAuthor = $this->repository->findOneBy(['email' => 'author2@example.com']);
        assert($knownAuthor instanceof Author);

        $authorByline = $this->faker->name();
        $authorEmail = $knownAuthor->getEmail();

        $this->commandTester->setInputs(['no']);
        $this->commandTester->execute([
            'authors' => "$authorByline <$authorEmail>",
        ]);

        $output = $this->commandTester->getDisplay();

        $this->assertSame(Command::FAILURE, $this->commandTester->getStatusCode());

        $this->assertStringContainsString(
            sprintf(
                "Do you want to change the byline for <%s> to '%s' from '%s'",
                $authorEmail,
                $authorByline,
                $knownAuthor->getByline(),
            ),
            $output,
        );

        $this->assertStringContainsString('Aborting...', $output);
    }

    #[TestDox('displays user not found message for unknown user ID')]
    public function testUserNotFound(): void
    {
        $authorByline = $this->faker->name();
        $authorEmail = $this->faker->email();

        $userId = $this->faker->uuid();

        $this->commandTester->execute([
            '--user-id' => $userId,
            'authors' => "$authorByline <$authorEmail>",
        ]);

        $output = $this->commandTester->getDisplay();

        $this->assertSame(Command::FAILURE, $this->commandTester->getStatusCode());
        $this->assertStringContainsString(
            sprintf('User with ID "%s" does not exist.', $userId),
            $output,
        );
    }
}
