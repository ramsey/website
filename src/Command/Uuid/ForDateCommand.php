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

namespace App\Command\Uuid;

use App\Console\Command;
use DateMalformedStringException;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function sprintf;

#[AsCommand(name: 'app:uuid:for-date', description: 'Generates a version 7 UUID for a given date')]
final class ForDateCommand extends Command
{
    protected function configure(): void
    {
        $this->addArgument(
            'date',
            InputArgument::OPTIONAL,
            'The date to generate a UUID for, or leave empty for the current date',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string | null $inputDate */
        $inputDate = $input->getArgument('date');

        try {
            $date = new DateTimeImmutable($inputDate ?? 'now');
        } catch (DateMalformedStringException) {
            $this->getStyle()->error("Invalid date string: '$inputDate'");

            return self::FAILURE;
        }

        $uuid = Uuid::uuid7($date);

        $this->getErrorStyle()->newLine();
        $this->getErrorStyle()->writeln(sprintf('<info>Your version 7 UUID for %s is:</info>', $date->format('c')));
        $this->getStyle()->write(sprintf('%s', $uuid->toString()));
        $this->getErrorStyle()->newLine();

        return self::SUCCESS;
    }
}
