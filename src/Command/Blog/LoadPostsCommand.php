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

namespace App\Command\Blog;

use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;
use Symfony\Component\Finder\Finder;
use Throwable;

use function sprintf;

#[AsCommand(
    name: 'app:blog:load-posts',
    description: 'Parses and loads/updates blog posts from a directory of static files',
)]
final class LoadPostsCommand extends Command
{
    public function __construct(
        private readonly Finder $finder,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(name: 'dry-run', description: 'Do not make any changes to the database')
            ->addArgument('path', InputArgument::REQUIRED, 'The path to the directory of static blog post files');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var string $path */
        $path = $input->getArgument('path');

        /** @var bool $isDryRun */
        $isDryRun = $input->getOption('dry-run');

        try {
            $this->finder->files()->name(['*.md', '*.markdown', '*.rst', '*.html'])->in($path);
        } catch (DirectoryNotFoundException $exception) {
            $io->getErrorStyle()->error($exception->getMessage());

            return Command::FAILURE;
        }

        if (!$this->finder->hasResults()) {
            $io->getErrorStyle()->info(sprintf('No posts found in %s', $path));

            return Command::SUCCESS;
        }

        try {
            $this->loadBlogPosts($this->finder, $output, $isDryRun);
        } catch (Throwable $exception) {
            $io->getErrorStyle()->error($exception->getMessage());

            return Command::FAILURE;
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }

    private function loadBlogPosts(Finder $finder, OutputInterface $output, bool $isDryRun): void
    {
        foreach ($finder as $file) {
            $absoluteFilePath = $file->getRealPath();

            $loadPostInput = new ArrayInput([
                'command' => 'app:blog:load-post',
                '--force' => true,
                '--save-deferred' => true,
                '--dry-run' => $isDryRun,
                'path' => $absoluteFilePath,
            ]);

            $returnCode = $this->getApplication()?->doRun($loadPostInput, $output);

            if ($returnCode !== Command::SUCCESS) {
                throw new RuntimeException('An error occurred while loading blog posts.');
            }
        }
    }
}
