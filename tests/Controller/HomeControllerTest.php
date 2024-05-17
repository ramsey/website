<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomeControllerTest extends WebTestCase
{
    #[TestDox('Request to / page returns successful response')]
    public function testHomePageResponse(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Ben Ramsey');
        $this->assertResponseHeaderSame(
            'cache-control',
            'max-age=28800, public, stale-while-revalidate=7200',
        );
    }
}
