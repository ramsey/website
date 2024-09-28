<?php

declare(strict_types=1);

namespace App\Tests\Command\Uuid;

use DateTime;
use DateTimeZone;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestDox;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;

use function assert;
use function sprintf;

#[Group('commands')]
#[TestDox('Command app:uuid:for-date')]
class ForDateCommandTest extends KernelTestCase
{
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        self::bootKernel();
        assert(self::$kernel instanceof KernelInterface);
        $app = new Application(self::$kernel);

        $command = $app->find('app:uuid:for-date');
        $this->commandTester = new CommandTester($command);
    }

    #[TestDox('creates a version 7 UUID')]
    public function testExecuteWithoutArgument(): void
    {
        $this->commandTester->execute([], ['capture_stderr_separately' => true]);
        $this->commandTester->assertCommandIsSuccessful();

        $output = $this->commandTester->getDisplay();
        $stderrOutput = $this->commandTester->getErrorOutput();

        $this->assertStringContainsString('Your version 7 UUID for', $stderrOutput);
        $this->assertTrue(Uuid::isValid($output));

        $uuid = Uuid::fromString($output);
        $this->assertSame(7, $uuid->getVersion());
    }

    #[TestDox('creates a version 7 UUID from the date argument')]
    public function testExecuteWithArgument(): void
    {
        $date = new DateTime('2024-07-28 11:27:38 -05:00');
        $date->setTimezone(new DateTimeZone('UTC'));

        $this->commandTester->execute([
            'date' => sprintf('@%s', $date->format('U')),
        ], [
            'capture_stderr_separately' => true,
        ]);

        $this->commandTester->assertCommandIsSuccessful();

        $output = $this->commandTester->getDisplay();
        $stderrOutput = $this->commandTester->getErrorOutput();

        $this->assertStringContainsString(sprintf('Your version 7 UUID for %s is:', $date->format('c')), $stderrOutput);
        $this->assertTrue(Uuid::isValid($output));

        $uuid = Uuid::fromString($output);
        $this->assertSame(7, $uuid->getVersion());
        $this->assertSame($date->format('c'), $uuid->getDateTime()->format('c'));
    }

    public function testExecuteWithInvalidDate(): void
    {
        $date = new DateTime('2024-07-28 11:27:38 -05:00');
        $date->setTimezone(new DateTimeZone('UTC'));

        $this->commandTester->execute(['date' => 'foobar']);

        $output = $this->commandTester->getDisplay();

        $this->assertSame(Command::FAILURE, $this->commandTester->getStatusCode());
        $this->assertStringContainsString("[ERROR] Invalid date string: 'foobar'", $output);
    }
}
