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

namespace App\Command\User;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use function sprintf;

/**
 * Creates a user entity and persists it to the database
 */
#[AsCommand(name: 'app:user:create', description: 'Create a new user')]
final class CreateCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                name:'role',
                mode: InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                description: 'A role to grant to the user',
            )
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the user')
            ->addArgument('email', InputArgument::REQUIRED, 'The email address of the user');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var list<'ROLE_USER' | 'ROLE_ADMIN' | 'ROLE_SUPER_ADMIN'> $roles */
        $roles = $input->getOption('role');

        /** @var string $name */
        $name = $input->getArgument('name');

        /** @var string $email */
        $email = $input->getArgument('email');

        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $passwordQuestion = (new Question('Password: '))
            ->setHidden(true)
            ->setHiddenFallback(false);

        /** @var string $plaintextPassword */
        $plaintextPassword = $helper->ask($input, $output, $passwordQuestion);

        $user = new User();
        $user
            ->setName($name)
            ->setEmail($email)
            ->setPassword($this->passwordHasher->hashPassword($user, $plaintextPassword))
            ->setRoles($roles);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $output->writeln('');
        $output->writeln(sprintf(
            'Created user <comment>%s</comment> with ID <comment>%s</comment>',
            $user->getName(),
            $user->getId(),
        ));

        return Command::SUCCESS;
    }
}
