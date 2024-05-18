<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class KeybaseControllerTest extends WebTestCase
{
    #[TestDox('Request to /.well-known/keybase.txt responds with 404 for unknown host')]
    public function testKeybaseResponseWhenNotFound(): void
    {
        $client = static::createClient();
        $client->request('GET', 'https://benramsey.dev/.well-known/keybase.txt');

        $this->assertResponseStatusCodeSame(404);
        $this->assertResponseHeaderSame(
            'cache-control',
            'max-age=604800, public, stale-while-revalidate=86400',
        );
    }

    #[TestDox('Request to /.well-known/keybase.txt responds successfully')]
    #[TestWith(['ben.ramsey.dev', 'I am an admin of https://ben.ramsey.dev'])]
    #[TestWith(['benramsey.com', 'I am an admin of https://benramsey.com'])]
    public function testKeybaseSuccessfulResponse(string $host, string $testContent): void
    {
        $client = static::createClient();
        $client->request('GET', "https://{$host}/.well-known/keybase.txt");

        $content = (string) $client->getResponse()->getContent();

        $this->assertStringContainsString($testContent, $content);
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'text/plain; charset=utf-8');
        $this->assertResponseHeaderSame(
            'cache-control',
            'max-age=604800, public, stale-while-revalidate=86400',
        );
    }
}
