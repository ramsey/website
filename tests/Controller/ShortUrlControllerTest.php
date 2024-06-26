<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[Group('db')]
class ShortUrlControllerTest extends WebTestCase
{
    #[TestDox('request to https://ben.ramsey.dev/su/foobar responds with a 404')]
    public function testShortUrlRespondsWithNotFound(): void
    {
        $client = static::createClient();
        $client->request('GET', 'https://ben.ramsey.dev/su/foobar');

        $this->assertResponseStatusCodeSame(404);
        $this->assertSelectorTextContains('h1', 'Not Found');
    }

    #[TestDox('request to https://bram.se/foobar responds with a 404')]
    public function testShortUrlRespondsWithNotFoundAtHostname(): void
    {
        $client = static::createClient();
        $client->request('GET', 'https://bram.se/foobar');

        $this->assertResponseStatusCodeSame(404);
        $this->assertSelectorTextContains('h1', 'Not Found');
    }

    #[TestDox('request to https://ben.ramsey.dev/su/custom1 responds with a redirect')]
    public function testShortUrlRespondsWithRedirectForCustomSlug(): void
    {
        $client = static::createClient();
        $client->request('GET', 'https://ben.ramsey.dev/su/custom1');

        $this->assertResponseStatusCodeSame(302);
        $this->assertResponseHeaderSame('location', 'https://example.com/another-long-url');
        $this->assertResponseHeaderSame('referrer-policy', 'unsafe-url');
        $this->assertResponseHeaderSame('content-security-policy', 'referrer always;');
    }

    #[TestDox('request to https://bram.se/custom1 responds with a redirect')]
    public function testShortUrlRespondsWithRedirectForCustomSlugAtHostname(): void
    {
        $client = static::createClient();
        $client->request('GET', 'https://bram.se/custom1');

        $this->assertResponseStatusCodeSame(302);
        $this->assertResponseHeaderSame('location', 'https://example.com/another-long-url');
        $this->assertResponseHeaderSame('referrer-policy', 'unsafe-url');
        $this->assertResponseHeaderSame('content-security-policy', 'referrer always;');
    }

    #[TestDox('request to https://ben.ramsey.dev/su/F0084R responds with a redirect')]
    public function testShortUrlRespondsWithRedirectForAutogeneratedSlug(): void
    {
        $client = static::createClient();
        $client->request('GET', 'https://ben.ramsey.dev/su/F0084R');

        $this->assertResponseStatusCodeSame(302);
        $this->assertResponseHeaderSame('location', 'https://example.com/this-is-a-long-url');
        $this->assertResponseHeaderSame('referrer-policy', 'unsafe-url');
        $this->assertResponseHeaderSame('content-security-policy', 'referrer always;');
    }

    #[TestDox('request to https://bram.se/F0084R responds with a redirect')]
    public function testShortUrlRespondsWithRedirectForAutogeneratedSlugAtHostname(): void
    {
        $client = static::createClient();
        $client->request('GET', 'https://bram.se/F0084R');

        $this->assertResponseStatusCodeSame(302);
        $this->assertResponseHeaderSame('location', 'https://example.com/this-is-a-long-url');
        $this->assertResponseHeaderSame('referrer-policy', 'unsafe-url');
        $this->assertResponseHeaderSame('content-security-policy', 'referrer always;');
    }
}
