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

use App\Console\Command;
use App\Entity\Author;
use App\Entity\AuthorLink;
use App\Entity\AuthorLinkType;
use App\Service\Entity\AuthorService;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Psr\Http\Message\UriFactoryInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

use function array_column;
use function sprintf;

/**
 * Adds a link to an author
 */
#[AsCommand(name: 'app:author:add-link', description: 'Add a link to an author')]
final class AddLinkCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AuthorService $authorService,
        private readonly UriFactoryInterface $uriFactory,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, "The author's email address.")
            ->addArgument(
                name: 'type',
                mode: InputArgument::REQUIRED,
                description: 'The link type.',
                suggestedValues: array_column(AuthorLinkType::cases(), 'value'),
            )
            ->addArgument('link', InputArgument::REQUIRED, 'The link URL to add.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $email */
        $email = $input->getArgument('email');

        /** @var string $type */
        $type = $input->getArgument('type');

        /** @var string $link */
        $link = $input->getArgument('link');

        try {
            /** @var Author $author */
            $author = $this->authorService->getRepository()->findOneBy(['email' => $email]);

            if ($author === null) {
                throw new InvalidArgumentException(sprintf('The author for email "%s" does not exist.', $email));
            }

            $linkType = AuthorLinkType::from($type);
            $uri = $link
                ? $this->uriFactory->createUri($link)
                : throw new InvalidArgumentException('The link cannot be an empty string.');
        } catch (Throwable $exception) {
            $this->logger->error($exception->getMessage());

            return self::FAILURE;
        }

        $authorLink = (new AuthorLink())
            ->setAuthor($author)
            ->setUrl($uri)
            ->setType($linkType);

        $this->entityManager->persist($authorLink);
        $this->entityManager->flush();

        $this->logger->info(sprintf('Link "%s" added for author %s.', $link, $author->getByline()));

        return self::SUCCESS;
    }
}
