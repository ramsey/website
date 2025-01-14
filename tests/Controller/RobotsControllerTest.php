<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('RobotsController')]
class RobotsControllerTest extends WebTestCase
{
    #[TestDox('responds successfully to /robots.txt request')]
    public function testRobotsTxt(): void
    {
        $client = static::createClient();
        $client->request('GET', '/robots.txt');

        /** @var Response $response */
        $response = $client->getResponse();

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'text/plain; charset=utf-8');
        $this->assertResponseHeaderSame('cache-control', 'max-age=86400, must-revalidate, public');

        $this->assertStringContainsString('User-agent: *', (string) $response->getContent());
    }

    #[TestDox('responds successfully to /ads.txt request')]
    public function testAdsTxt(): void
    {
        $client = static::createClient();
        $client->request('GET', '/ads.txt');

        /** @var Response $response */
        $response = $client->getResponse();

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'text/plain; charset=utf-8');
        $this->assertResponseHeaderSame(
            'cache-control',
            'max-age=604800, public, stale-while-revalidate=86400',
        );

        $this->assertStringContainsString(
            'placeholder.example.com, placeholder, DIRECT, placeholder',
            (string) $response->getContent(),
        );
    }

    #[TestDox('responds successfully to /app-ads.txt request')]
    public function testAppAdsTxt(): void
    {
        $client = static::createClient();
        $client->request('GET', '/app-ads.txt');

        /** @var Response $response */
        $response = $client->getResponse();

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'text/plain; charset=utf-8');
        $this->assertResponseHeaderSame(
            'cache-control',
            'max-age=604800, public, stale-while-revalidate=86400',
        );

        $this->assertStringContainsString(
            'placeholder.example.com, placeholder, DIRECT, placeholder',
            (string) $response->getContent(),
        );
    }
}
