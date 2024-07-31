<?php

declare(strict_types=1);

namespace App\Tests\Command\ShortUrl;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;

use function assert;

#[Group('commands')]
#[Group('db')]
#[TestDox('Command app:short-url:create')]
final class CreateCommandTest extends KernelTestCase
{
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        self::bootKernel();
        assert(self::$kernel instanceof KernelInterface);
        $app = new Application(self::$kernel);

        $command = $app->find('app:short-url:create');
        $this->commandTester = new CommandTester($command);
    }

    #[TestDox('creates a short URL with a custom slug')]
    public function testExecuteWithOption(): void
    {
        $this->commandTester->execute([
            '--custom-slug' => 'url-from-command',
            'url' => 'https://example.com/create-short-url-from-console-command-with-custom-slug',
        ]);

        $this->commandTester->assertCommandIsSuccessful();

        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString('https://localhost/su/url-from-command', $output);
    }

    #[TestDox('creates a short URL with a random slug')]
    public function testExecuteWithoutOption(): void
    {
        $this->commandTester->execute([
            'url' => 'https://example.com/create-short-url-from-console-command-without-custom-slug',
        ]);

        $this->commandTester->assertCommandIsSuccessful();

        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString('https://localhost/su/', $output);
    }
}
