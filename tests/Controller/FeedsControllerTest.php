<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FeedsControllerTest extends WebTestCase
{
    #[TestDox('Request to /feeds/blog.xml returns success response')]
    public function testResponse(): void
    {
        $client = static::createClient();
        $client->request('GET', '/feeds/blog.xml');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'text/xml; charset=utf-8');
        $this->assertResponseHeaderSame(
            'cache-control',
            'max-age=604800, public, stale-while-revalidate=86400',
        );

        $this->assertStringContainsString(
            'feed is licensed under a Creative Commons',
            (string) $client->getResponse()->getContent(),
        );
    }
}
