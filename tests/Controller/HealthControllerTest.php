<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HealthControllerTest extends WebTestCase
{
    #[TestDox('gets a successful response from /health')]
    public function testHealthyResponse(): void
    {
        $client = static::createClient();
        $client->request('GET', '/health');

        $this->assertResponseIsSuccessful();
    }

    #[TestDox('gets a 503 response from /health')]
    public function testUnhealthyResponse(): void
    {
        $client = static::createClient(['environment' => 'test_no_db']);
        $client->request('GET', '/health');

        $this->assertResponseStatusCodeSame(503);
        $this->assertResponseHasHeader('retry-after');
        $this->assertResponseHeaderSame('retry-after', '10');
    }
}
