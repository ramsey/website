<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UnknownUriControllerTest extends WebTestCase
{
    #[TestDox('Request to /http-status-codes responds with 302 redirect')]
    public function testFoundRedirect(): void
    {
        $client = static::createClient();
        $client->request('GET', '/http-status-codes');

        $this->assertResponseStatusCodeSame(302);
        $this->assertSelectorTextContains('h1', 'Temporary Redirect');
        $this->assertResponseHeaderSame(
            'location',
            'http://localhost/blog/2008/04/http-status-100-continue/',
        );
    }

    #[TestDox('Request to /keys/benramsey.asc responds with 307 redirect')]
    public function testTemporaryRedirect(): void
    {
        $client = static::createClient();
        $client->request('GET', '/keys/benramsey.asc');

        $this->assertResponseStatusCodeSame(307);
        $this->assertSelectorTextContains('h1', 'Temporary Redirect');
        $this->assertResponseHeaderSame(
            'location',
            'https://static.ben.ramsey.dev/pgp-keys/benramsey-9C8C071B.asc',
        );
    }

    #[TestDox('Request to /archive responds with 301 redirect')]
    public function testPermanentRedirect(): void
    {
        $client = static::createClient();
        $client->request('GET', '/archive/index.html');

        $this->assertResponseStatusCodeSame(301);
        $this->assertSelectorTextContains('h1', 'Permanent Redirect');
        $this->assertResponseHeaderSame('location', 'http://localhost/blog');
    }

    #[TestDox('Request to /search responds with 410')]
    public function testGoneResponse(): void
    {
        $client = static::createClient();
        $client->request('GET', '/search/');

        $this->assertResponseStatusCodeSame(410);
        $this->assertSelectorTextContains('h1', 'Gone');
    }

    #[TestDox('Request to /this-should-never-be-a-valid-route responds with 404')]
    public function testNotFoundResponse(): void
    {
        $client = static::createClient();
        $client->request('GET', '/this-should-never-be-a-valid-route');

        $this->assertResponseStatusCodeSame(404);
        $this->assertSelectorTextContains('h1', 'Not Found');
    }
}
