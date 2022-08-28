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

        $bodyText = $crawler->text();

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Yak Shaving Is the Entire Job Description', $bodyText);
        $this->assertStringContainsString('PHPCommunity.org', $bodyText);
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

    public function testBlogPostSplitWithImageTemplate(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/blog/2016/phptek-tips');

        $html = $crawler->html();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', '7 Tips for php[tek]');

        $this->assertStringContainsString(
            'https://files.benramsey.com/ws/blog/2016-05-22-phptek-tips/banner-1500x630.jpg',
            $html,
        );
    }
}
