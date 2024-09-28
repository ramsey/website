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

namespace App\Console;

use DateTimeInterface;
use LogicException;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LogLevel;
use Psr\Log\LoggerTrait;
use Stringable;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\AbstractDumper;
use Symfony\Component\VarDumper\Dumper\CliDumper;

use function assert;
use function gettype;
use function is_bool;
use function is_object;
use function is_scalar;
use function sprintf;
use function str_contains;
use function strtoupper;
use function strtr;

final class ConsoleLogger implements IoAwareLogger
{
    use LoggerTrait;

    public const string EMERGENCY_STYLE = 'fg=bright-white;bg=bright-red;options=bold,blink';
    public const string ALERT_STYLE = 'fg=bright-white;bg=bright-red';
    public const string CRITICAL_STYLE = 'fg=black;bg=red';
    public const string ERROR_STYLE = 'fg=red';
    public const string WARNING_STYLE = 'fg=bright-yellow';
    public const string NOTICE_STYLE = 'fg=blue';
    public const string INFO_STYLE = 'fg=green';
    public const string DEBUG_STYLE = 'fg=bright-white;bg=gray';

    /**
     * @var array<string, int>
     */
    private array $verbosityLevelMap = [
        LogLevel::EMERGENCY => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::ALERT => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::CRITICAL => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::ERROR => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::WARNING => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::INFO => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::DEBUG => OutputInterface::VERBOSITY_VERBOSE,
    ];

    /**
     * @var array<string, string>
     */
    private array $formatLevelMap = [
        LogLevel::EMERGENCY => self::EMERGENCY_STYLE,
        LogLevel::ALERT => self::ALERT_STYLE,
        LogLevel::CRITICAL => self::CRITICAL_STYLE,
        LogLevel::ERROR => self::ERROR_STYLE,
        LogLevel::WARNING => self::WARNING_STYLE,
        LogLevel::NOTICE => self::NOTICE_STYLE,
        LogLevel::INFO => self::INFO_STYLE,
        LogLevel::DEBUG => self::DEBUG_STYLE,
    ];

    private ?SymfonyStyle $io = null;
    private VarCloner $varCloner;
    private CliDumper $varDumper;

    public function __construct()
    {
        $this->varCloner = new VarCloner();
        $this->varDumper = new CliDumper(flags: AbstractDumper::DUMP_LIGHT_ARRAY | AbstractDumper::DUMP_TRAILING_COMMA);
    }

    /**
     * @param bool | float | int | string $level
     *
     * @inheritDoc
     */
    public function log($level, Stringable | string $message, array $context = []): void
    {
        assert(is_scalar($level));

        if (!isset($this->verbosityLevelMap[$level])) {
            throw new InvalidArgumentException(sprintf('The log level "%s" does not exist', $level));
        }

        if ($this->getIo()->getVerbosity() >= $this->verbosityLevelMap[$level]) {
            $this->getIo()->block(
                messages: $this->interpolate((string) $message, $context),
                type: strtoupper((string) $level),
                style: $this->formatLevelMap[$level],
                padding: true,
            );
        }

        if ($context !== [] && $this->getIo()->isDebug()) {
            $dump = $this->varDumper->dump($this->varCloner->cloneVar($context), true);
            $this->getIo()->block(messages: ['Context:', (string) $dump], prefix: '    ');
        }
    }

    public function setIo(InputInterface $input, OutputInterface $output): void
    {
        $this->io = (new SymfonyStyle($input, $output))->getErrorStyle();
    }

    private function getIo(): SymfonyStyle
    {
        if ($this->io === null) {
            throw new LogicException('You must call setIo() before calling this method');
        }

        return $this->io;
    }

    /**
     * Interpolates context values into the message placeholders.
     *
     * @param mixed[] $context
     */
    private function interpolate(string $message, array $context): string
    {
        if (!str_contains($message, '{')) {
            return $message;
        }

        $replacements = [];
        foreach ($context as $key => $val) {
            if ($val === null) {
                $replacements["{{$key}}"] = 'NULL';
            } elseif (is_bool($val)) {
                $replacements["{{$key}}"] = $val ? 'TRUE' : 'FALSE';
            } elseif ($val instanceof DateTimeInterface) {
                $replacements["{{$key}}"] = $val->format(DateTimeInterface::RFC3339);
            } elseif (is_scalar($val) || $val instanceof Stringable) {
                $replacements["{{$key}}"] = $val;
            } elseif (is_object($val)) {
                $replacements["{{$key}}"] = '[object ' . $val::class . ']';
            } else {
                $replacements["{{$key}}"] = '[' . gettype($val) . ']';
            }
        }

        return strtr($message, $replacements);
    }
}
