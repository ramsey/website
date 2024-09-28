<?php

declare(strict_types=1);

namespace App\Tests\Console;

use App\Console\ConsoleLogger;
use DateTimeImmutable;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use stdClass;

use function preg_replace;
use function trim;

class ConsoleLoggerTest extends TestCase
{
    public function testLogThrowsExceptionForInvalidLogLevel(): void
    {
        $logger = new ConsoleLogger();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The log level "foo" does not exist');

        $logger->log('foo', 'this is a log message');
    }

    public function testLogThrowsExceptionWhenIoIsNotSet(): void
    {
        $logger = new ConsoleLogger();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('You must call setIo() before calling this method');

        $logger->log('warning', 'this is another log message');
    }

    public function testLogWithInterpolationValues(): void
    {
        $uuid = Uuid::fromString('01921bd8-265e-7307-ab63-c5df01924ad2');
        $input = new StringInput('');
        $output = new BufferedOutput();

        $logger = new ConsoleLogger();
        $logger->setIo($input, $output);

        $logger->log(
            'error',
            'logging something: {aDate}, {aNull}, {aTrue}, {aFalse}, {anInt}, {aFloat}, {aString}, '
                . '{aStringable}, {anObject}, {anArray}',
            [
                'aDate' => new DateTimeImmutable('@1727043862'),
                'aNull' => null,
                'aTrue' => true,
                'aFalse' => false,
                'anInt' => 12345,
                'aFloat' => 123.45,
                'aString' => 'some string',
                'aStringable' => $uuid,
                'anObject' => new stdClass(),
                'anArray' => [1, 2, 3],
            ],
        );

        $logOutput = trim((string) preg_replace('/\s+/', ' ', $output->fetch()));

        $this->assertStringContainsString(
            '[ERROR] logging something: 2024-09-22T22:24:22+00:00, NULL, TRUE, FALSE, 12345, 123.45, some string, '
                . '01921bd8-265e-7307-ab63-c5df01924ad2, [object stdClass], [array]',
            $logOutput,
        );
        $this->assertStringNotContainsString('Context:', $logOutput);
    }

    public function testLogWithNoInterpolationValues(): void
    {
        $input = new StringInput('');
        $output = new BufferedOutput();

        $logger = new ConsoleLogger();
        $logger->setIo($input, $output);

        $logger->log('error', 'logging something', [
            'context1' => 1,
            'context2' => 2,
        ]);

        $logOutput = $output->fetch();

        $this->assertStringContainsString('[ERROR] logging something', $logOutput);
        $this->assertStringNotContainsString('Context:', $logOutput);
    }

    public function testLogWithLevelOutsideVerbosityDoesNotIncludeLogMessage(): void
    {
        $input = new StringInput('');
        $output = new BufferedOutput();

        $logger = new ConsoleLogger();
        $logger->setIo($input, $output);

        $logger->log('debug', 'this message should not be in the output');

        $logOutput = $output->fetch();

        $this->assertSame('', $logOutput);
    }

    public function testLogWithIncreasedVerbosityIncludesLogMessage(): void
    {
        $input = new StringInput('');
        $output = new BufferedOutput(OutputInterface::VERBOSITY_VERBOSE);

        $logger = new ConsoleLogger();
        $logger->setIo($input, $output);

        $logger->log('debug', 'this message should be in the output');

        $logOutput = $output->fetch();

        $this->assertStringContainsString('this message should be in the output', $logOutput);
        $this->assertStringNotContainsString('Context:', $logOutput, 'Context should not be in the output');
    }

    public function testLogWithVerboseLoggingIncludesContext(): void
    {
        $input = new StringInput('');
        $output = new BufferedOutput(OutputInterface::VERBOSITY_DEBUG);

        $logger = new ConsoleLogger();
        $logger->setIo($input, $output);

        $logger->log('debug', 'debugging something', [
            'context1' => 1,
            'context2' => 2,
        ]);

        $logOutput = $output->fetch();

        $this->assertStringContainsString('[DEBUG] debugging something', $logOutput);
        $this->assertStringContainsString('Context:', $logOutput);
    }
}
