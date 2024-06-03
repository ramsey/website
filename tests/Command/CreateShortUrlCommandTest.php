<?php

declare(strict_types=1);

namespace App\Tests\Command;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;

use function assert;

#[Group(name: 'commands')]
final class CreateShortUrlCommandTest extends KernelTestCase
{
    #[TestDox('app:short-url:create command creates URL with custom slug')]
    public function testExecuteWithOption(): void
    {
        self::bootKernel();
        assert(self::$kernel instanceof KernelInterface);
        $app = new Application(self::$kernel);

        $command = $app->find('app:short-url:create');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            '--custom-slug' => 'url-from-command',
            'url' => 'https://example.com/create-short-url-from-console-command-with-custom-slug',
        ]);

        $commandTester->assertCommandIsSuccessful();

        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('https://localhost:8000/su/url-from-command', $output);
    }

    #[TestDox('app:short-url:create command creates URL without custom slug')]
    public function testExecuteWithoutOption(): void
    {
        self::bootKernel();
        assert(self::$kernel instanceof KernelInterface);
        $app = new Application(self::$kernel);

        $command = $app->find('app:short-url:create');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'url' => 'https://example.com/create-short-url-from-console-command-without-custom-slug',
        ]);

        $commandTester->assertCommandIsSuccessful();

        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('https://localhost:8000/su/', $output);
    }
}
