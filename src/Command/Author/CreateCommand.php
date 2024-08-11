<?php

/**
 * This file is part of ramsey/website
 *
 * Copyright (c) Ben Ramsey <ben@ramsey.dev>
 *
 * ramsey/website is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Affero General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.
 *
 * ramsey/website is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with ramsey/website. If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace App\Command\Author;

use App\Repository\UserRepository;
use App\Service\Entity\AuthorService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PhpExtended\Email\MailboxList;
use PhpExtended\Email\MailboxListParserInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function sprintf;
use function trim;

/**
 * Creates an author entity and persists it to the database
 */
#[AsCommand(name: 'app:author:create', description: 'Create a new author')]
final class CreateCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AuthorService $authorService,
        private readonly UserRepository $userRepository,
        private readonly MailboxListParserInterface $mailboxListParser,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('user-id', null, InputOption::VALUE_REQUIRED, 'A user ID to associate with the author')
            ->addArgument(
                'authors',
                InputArgument::REQUIRED,
                'A comma-separated list of email addresses with optional display names (i.e., "Frodo Baggins '
                    . '<frodo@example.com>, Samwise Gamgee <samwise@example.com>"). Note that any user-id provided '
                    . 'be associated with all authors listed.',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var string | null $userId */
        $userId = $input->getOption('user-id');

        /** @var string $authorsInput */
        $authorsInput = $input->getArgument('authors');

        /** @var MailboxList $mailboxList */
        $mailboxList = $this->mailboxListParser->parse($authorsInput);

        $user = null;
        if ($userId !== null) {
            $user = $this->userRepository->find($userId);

            if ($user === null) {
                $io->getErrorStyle()->error(sprintf('User with ID "%s" does not exist.', $userId));

                return Command::FAILURE;
            }
        }

        $authors = [];
        foreach ($mailboxList as $mailbox) {
            $byline = trim($mailbox->getDisplayName());
            $email = $mailbox->getEmailAddress()->getCanonicalRepresentation();
            $author = $this->authorService->getRepository()->findOneBy(['email' => $email])
                ?? $this->authorService->createAuthor($byline, $email);

            if ($this->entityManager->contains($author)) {
                $question = sprintf(
                    "Do you want to change the byline for <%s> to '%s' from '%s'",
                    $email,
                    $byline,
                    $author->getByline(),
                );

                if ($author->getByline() !== $byline && !$io->confirm($question, false)) {
                    $io->getErrorStyle()->warning('Aborting...');

                    return Command::FAILURE;
                }

                $author->setByline($byline);
                $author->setUpdatedAt(new DateTimeImmutable());
            }

            if ($user !== null) {
                $author->setUser($user);
            }

            $this->entityManager->persist($author);
            $authors[] = $author;
        }

        $this->entityManager->flush();

        $io->newLine();

        $table = $io->createTable();
        $table->setHeaderTitle('Authors');
        $table->setHeaders(['Byline', 'Email', 'ID']);

        foreach ($authors as $author) {
            $table->addRow([$author->getByline(), $author->getEmail(), $author->getId()]);
        }

        $table->render();

        if ($user !== null) {
            $io->info(sprintf(
                'All authors are associated with user "%s" (%s).',
                $user->getName(),
                $user->getId(),
            ));
        }

        return Command::SUCCESS;
    }
}
