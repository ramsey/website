<?php

declare(strict_types=1);

namespace App\Tests\Console;

use App\Console\Command;
use LogicException;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\StyleInterface;

class CommandTest extends TestCase
{
    private Command $command;

    protected function setUp(): void
    {
        $this->command = new class () extends Command {
            public function callInitialize(InputInterface $input, OutputInterface $output): void
            {
                $this->initialize($input, $output);
            }
        };
    }

    public function testLogger(): void
    {
        $logger = new NullLogger();
        $this->command->setLogger($logger);

        $this->assertSame($logger, $this->command->getLogger());
    }

    public function testGetStyleThrowsExceptionWhenInitializedNotCalled(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('You must call initialize() before calling this method');

        $this->command->getStyle();
    }

    public function testGetErrorStyleThrowsExceptionWhenInitializedNotCalled(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('You must call initialize() before calling this method');

        $this->command->getStyle();
    }

    public function testGetStyle(): void
    {
        $input = new ArgvInput();
        $output = new ConsoleOutput();

        /** @phpstan-ignore-next-line */
        $this->command->callInitialize($input, $output);

        $this->assertInstanceOf(StyleInterface::class, $this->command->getStyle());
    }

    public function testGetErrorStyle(): void
    {
        $input = new ArgvInput();
        $output = new ConsoleOutput();

        /** @phpstan-ignore-next-line */
        $this->command->callInitialize($input, $output);

        $this->assertInstanceOf(StyleInterface::class, $this->command->getErrorStyle());
    }
}
