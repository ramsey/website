<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CopyrightControllerTest extends WebTestCase
{
    #[TestDox('Request to /copyright page returns successful response')]
    public function testCopyrightPageResponse(): void
    {
        $client = static::createClient();
        $client->request('GET', '/copyright');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Copyright');
        $this->assertResponseHeaderSame(
            'cache-control',
            'max-age=604800, public, stale-while-revalidate=86400',
        );
    }
}
