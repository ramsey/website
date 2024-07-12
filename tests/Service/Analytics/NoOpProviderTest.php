<?php

declare(strict_types=1);

namespace App\Tests\Service\Analytics;

use App\Service\Analytics\AnalyticsDetails;
use App\Service\Analytics\NoOpProvider;
use Laminas\Diactoros\UriFactory;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('NoOpProvider')]
class NoOpProviderTest extends TestCase
{
    #[TestDox('recordEventFromWebContext() does not perform any operations')]
    public function testNoOpFromWebContext(): void
    {
        $this->expectNotToPerformAssertions();

        $provider = new NoOpProvider();
        $request = Request::create('https://example.com/');
        $response = new Response();

        $provider->recordEventFromWebContext('no-op-event', $request, $response, []);
    }

    #[TestDox('recordEventFromDetails() does not perform any operations')]
    public function testNoOpFromDetails(): void
    {
        $this->expectNotToPerformAssertions();

        $provider = new NoOpProvider();
        $details = new AnalyticsDetails(
            eventName: 'no-op-event',
            url: (new UriFactory())->createUri('https://example.com/'),
        );

        $provider->recordEventFromDetails($details);
    }
}
