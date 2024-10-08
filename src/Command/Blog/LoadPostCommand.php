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

use App\Console\Command;
use App\Console\ConfirmationQuestionDeclined;
use App\Entity\Post;
use App\Service\Blog\ParsedPost;
use App\Service\Blog\PostParser;
use App\Service\Entity\EntityExists;
use App\Service\Entity\PostManager;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function sprintf;

#[AsCommand(
    name: 'app:blog:load-post',
    description: 'Parses and loads/updates a blog post from a static file',
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
        $this
            ->addOption(name: 'dry-run', description: 'Do not make any changes to the database')
            ->addOption(name: 'force', description: 'Do not prompt for confirmation if the blog post already exists')
            ->addOption(name: 'save-deferred', description: 'Defer saving; use only when called from another command!')
            ->addArgument('path', InputArgument::REQUIRED, 'The path to the static blog post file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $path */
        $path = $input->getArgument('path');

        /** @var bool $isDryRun */
        $isDryRun = $input->getOption('dry-run');

        /** @var bool $isForced */
        $isForced = $input->getOption('force');

        /** @var bool $isSaveDeferred */
        $isSaveDeferred = $input->getOption('save-deferred');

        try {
            $post = $this->createPostForSaving($this->postParser->parse($path), $isForced);
        } catch (ConfirmationQuestionDeclined $exception) {
            $this->getStyle()->warning($exception->getMessage());

            return self::FAILURE;
        } catch (InvalidArgumentException $exception) {
            $this->getStyle()->error($exception->getMessage());

            return self::FAILURE;
        }

        if (!$isDryRun) {
            $this->saveToDatabase($post, $isSaveDeferred);
        }

        $this->getStyle()->writeln(sprintf(
            '%s<info>Saved blog post for %s: "%s"</info>',
            $isDryRun ? '<comment>[DRY-RUN]</comment> ' : '',
            $post->getCreatedAt()?->format('Y-m-d'),
            $post->getTitle(),
        ));

        return self::SUCCESS;
    }

    private function createPostForSaving(ParsedPost $parsedPost, bool $isForced): Post
    {
        try {
            $post = $this->postManager->upsertFromParsedPost($parsedPost, doUpdate: $isForced);
        } catch (EntityExists) {
            $question = sprintf(
                'A post with ID <comment>%s</comment> already exists. Do you want to update it?',
                $parsedPost->metadata->id,
            );

            if (!$this->getStyle()->confirm($question)) {
                throw new ConfirmationQuestionDeclined('Aborting...');
            }

            $post = $this->postManager->upsertFromParsedPost($parsedPost, doUpdate: true);
        }

        return $post;
    }

    private function saveToDatabase(Post $post, bool $isSaveDeferred): void
    {
        $this->entityManager->persist($post);

        // If saving is deferred, then this should be called from another internal
        // command that has access to the entity manager and can flush it.
        if (!$isSaveDeferred) {
            $this->entityManager->flush();
        }
    }
}
