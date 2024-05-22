<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    #[TestDox('Request to /.well-known/security.txt returns success response')]
    public function testSecurityResponse(): void
    {
        $client = static::createClient();
        $client->request('GET', '/.well-known/security.txt');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'text/plain; charset=utf-8');
        $this->assertResponseHeaderSame(
            'cache-control',
            'max-age=604800, public, stale-while-revalidate=86400',
        );

        $this->assertStringContainsString(
            'Canonical: https://ben.ramsey.dev/.well-known/security.txt',
            (string) $client->getResponse()->getContent(),
        );
    }
}
