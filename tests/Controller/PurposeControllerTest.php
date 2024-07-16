<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PurposeControllerTest extends WebTestCase
{
    #[TestDox('Request to /purpose page returns successful response')]
    public function testPurposeResponse(): void
    {
        $client = static::createClient();
        $client->request('GET', '/purpose');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Purpose');
        $this->assertResponseHeaderSame(
            'cache-control',
            'max-age=604800, public, stale-while-revalidate=86400',
        );
    }
}
