<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MatrixControllerTest extends WebTestCase
{
    #[TestDox('Request to /.well-known/matrix/client returns success response')]
    public function testMatrixClientResponse(): void
    {
        $client = static::createClient();
        $client->request('GET', '/.well-known/matrix/client');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
        $this->assertResponseHeaderSame('access-control-allow-origin', '*');
        $this->assertResponseHeaderSame(
            'cache-control',
            'max-age=604800, public, stale-while-revalidate=86400',
        );
    }

    #[TestDox('Request to /.well-known/matrix/server returns success response')]
    public function testMatrixServerResponse(): void
    {
        $client = static::createClient();
        $client->request('GET', '/.well-known/matrix/server');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
        $this->assertResponseHeaderSame('access-control-allow-origin', '*');
        $this->assertResponseHeaderSame(
            'cache-control',
            'max-age=604800, public, stale-while-revalidate=86400',
        );
    }
}
