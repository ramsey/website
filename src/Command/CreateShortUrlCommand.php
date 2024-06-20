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

namespace App\Command;

use App\Repository\UserRepository;
use App\Service\ShortUrlManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Creates a short URL and persists it to the database
 */
#[AsCommand(name: 'app:short-url:create', description: 'Add a new short URL to the database')]
final class CreateShortUrlCommand extends Command
{
    public function __construct(
        private readonly ShortUrlManager $shortUrlManager,
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('custom-slug', 's', InputOption::VALUE_OPTIONAL, 'A custom short URL slug', null)
            ->addArgument('url', InputArgument::REQUIRED, 'The URL to redirect to')
            ->addArgument('email', InputArgument::REQUIRED, 'The email address of the user to associate as "creator"');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $url */
        $url = $input->getArgument('url');

        /** @var string $email */
        $email = $input->getArgument('email');

        /** @var string|null $slug */
        $slug = $input->getOption('custom-slug');

        $user = $this->userRepository->findOneBy(['email' => $email]);

        if ($user === null) {
            throw new InvalidArgumentException("User with email '$email' does not exist");
        }

        $shortUrl = $this->shortUrlManager->createShortUrl($url, $user, $slug);

        $this->entityManager->persist($shortUrl);
        $this->entityManager->flush();

        $output->writeln([(string) $this->shortUrlManager->buildUrl($shortUrl)]);

        return Command::SUCCESS;
    }
}
