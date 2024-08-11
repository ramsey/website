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

use App\Service\Blog\PostParser;
use App\Service\Entity\PostManager;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function sprintf;
use function ucfirst;

#[AsCommand(
    name: 'app:blog:load-post',
    description: 'Parses and loads/updates a blog post in the database from a static file',
)]
final class LoadPostCommand extends Command
{
    public function __construct(
        private readonly PostParser $postParser,
        private readonly PostManager $postManager,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('path', InputArgument::REQUIRED, 'The path to the static blog post file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var string $path */
        $path = $input->getArgument('path');

        try {
            $parsedPost = $this->postParser->parse($path);
        } catch (InvalidArgumentException $exception) {
            $io->getErrorStyle()->error($exception->getMessage());

            return Command::FAILURE;
        }

        $existingPost = $this->postManager->getRepository()->find($parsedPost->metadata->id);

        if ($existingPost !== null) {
            $question = sprintf(
                'A post with ID <comment>%s</comment> already exists. Do you want to update it?',
                $existingPost->getId(),
            );

            if (!$io->confirm($question)) {
                $io->getErrorStyle()->warning('Aborting...');

                return Command::FAILURE;
            }

            $action = 'updated';
            $post = $this->postManager->updateFromParsedPost($existingPost, $parsedPost);
        } else {
            $action = 'created';
            $post = $this->postManager->createFromParsedPost($parsedPost);
        }

        $this->entityManager->persist($post);
        $this->entityManager->flush();

        $io->info(sprintf('%s blog post with ID %s', ucfirst($action), $post->getId()));

        return Command::SUCCESS;
    }
}
