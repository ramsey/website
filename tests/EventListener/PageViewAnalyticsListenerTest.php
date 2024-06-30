<?php

declare(strict_types=1);

namespace App\Tests\EventListener;

use App\EventListener\PageViewAnalyticsListener;
use App\Service\Analytics\AnalyticsService;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class PageViewAnalyticsListenerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testPageViewAnalyticsListener(): void
    {
        $kernel = Mockery::mock(HttpKernelInterface::class);
        $request = Mockery::mock(Request::class);
        $response = Mockery::mock(Response::class);

        $service = Mockery::mock(AnalyticsService::class);
        $service->expects('recordEvent')->with('pageview', $request, $response);

        $listener = new PageViewAnalyticsListener($service);
        $listener(new TerminateEvent($kernel, $request, $response));
    }
}
