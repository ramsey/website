<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class FeedsControllerTest extends WebTestCase
{
    #[TestDox('Request to /feeds/blog.xml returns success response')]
    public function testBlogFeed(): void
    {
        $client = static::createClient();
        $client->request('GET', '/feeds/blog.xml');

        /** @var Response $response */
        $response = $client->getResponse();

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'text/xml; charset=utf-8');
        $this->assertResponseHeaderSame(
            'cache-control',
            'max-age=3600, must-revalidate, public',
        );

        $this->assertStringContainsString(
            'feed is licensed under a Creative Commons',
            (string) $response->getContent(),
        );
    }

    #[TestDox('Request to /sitemap.xml returns success response')]
    public function testSitemap(): void
    {
        $client = static::createClient();
        $client->request('GET', '/sitemap.xml');

        /** @var Response $response */
        $response = $client->getResponse();

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'text/xml; charset=utf-8');
        $this->assertResponseHeaderSame(
            'cache-control',
            'max-age=3600, must-revalidate, public',
        );

        $this->assertStringContainsString(
            'www.sitemaps.org',
            (string) $response->getContent(),
        );
    }
}
