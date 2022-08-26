<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BlogControllerTest extends WebTestCase
{
    public function testBlogListRedirectsTrailingSlash(): void
    {
        $client = static::createClient();
        $client->request('GET', '/blog/');

        $this->assertResponseRedirects('http://localhost/blog', 301);
    }

    public function testListOfBlogPosts(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/blog');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString(
            'List of blog posts',
            $crawler->text(),
        );
    }

    public function testBlogPostRedirectsTrailingSlash(): void
    {
        $client = static::createClient();
        $client->request('GET', '/blog/2004/phpcommunityorg/');

        $this->assertResponseRedirects('http://localhost/blog/2004/phpcommunityorg', 301);
    }

    public function testBlogPost(): void
    {
        $client = static::createClient();
        $client->request('GET', '/blog/2004/phpcommunityorg');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'PHPCommunity.org');
    }
}
