<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PageControllerTest extends WebTestCase
{
    public function testPage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/page');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Hello, World!');
    }

    public function testPageRedirectsTrailingSlash(): void
    {
        $client = static::createClient();
        $client->request('GET', '/page/');

        $this->assertResponseRedirects('http://localhost/page', 301);
    }
}
