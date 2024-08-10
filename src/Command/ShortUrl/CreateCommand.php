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

namespace App\Command\ShortUrl;

use App\Service\Entity\ShortUrlManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Creates a short URL and persists it to the database
 */
#[AsCommand(name: 'app:short-url:create', description: 'Add a new short URL to the database')]
final class CreateCommand extends Command
{
    public function __construct(
        private readonly ShortUrlManager $shortUrlManager,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('custom-slug', 's', InputOption::VALUE_OPTIONAL, 'A custom short URL slug', null)
            ->addArgument('url', InputArgument::REQUIRED, 'The URL to redirect to');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $url */
        $url = $input->getArgument('url');

        /** @var string|null $slug */
        $slug = $input->getOption('custom-slug');

        $shortUrl = $this->shortUrlManager->createShortUrl($url, $slug);

        $this->entityManager->persist($shortUrl);
        $this->entityManager->flush();

        $output->writeln([(string) $this->shortUrlManager->buildUrl($shortUrl)]);

        return Command::SUCCESS;
    }
}
