<?php

declare(strict_types=1);

namespace App\Tests\EventListener;

use App\EventListener\PageViewAnalyticsListener;
use App\Service\Analytics\AnalyticsService;
use App\Service\Analytics\UnknownAnalyticsDomain;
use LogicException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

#[TestDox('PageViewAnalyticsListener')]
class PageViewAnalyticsListenerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[TestDox('calls recordEvent() on the AnalyticsService')]
    public function testPageViewAnalyticsListener(): void
    {
        $kernel = Mockery::mock(HttpKernelInterface::class);
        $response = Mockery::mock(Response::class);
        $request = Mockery::mock(Request::class);

        $service = Mockery::mock(AnalyticsService::class);
        $service
            ->expects('recordEventFromWebContext')
            ->with('pageview', $request, $response);

        $listener = new PageViewAnalyticsListener($service);
        $listener(new TerminateEvent($kernel, $request, $response));
    }

    #[TestDox('handles UnknownAnalyticsDomain exceptions')]
    public function testPageViewAnalyticsWithUnknownAnalyticsDomainException(): void
    {
        $kernel = Mockery::mock(HttpKernelInterface::class);
        $response = Mockery::mock(Response::class);
        $request = Mockery::mock(Request::class);

        $service = Mockery::mock(AnalyticsService::class);
        $service
            ->expects('recordEventFromWebContext')
            ->with('pageview', $request, $response)
            ->andThrow(new UnknownAnalyticsDomain());

        $listener = new PageViewAnalyticsListener($service);
        $listener(new TerminateEvent($kernel, $request, $response));
    }

    #[TestDox('does not handle other exceptions')]
    public function testPageViewAnalyticsWithOtherException(): void
    {
        $kernel = Mockery::mock(HttpKernelInterface::class);
        $response = Mockery::mock(Response::class);
        $request = Mockery::mock(Request::class);

        $service = Mockery::mock(AnalyticsService::class);
        $service
            ->expects('recordEventFromWebContext')
            ->with('pageview', $request, $response)
            ->andThrow(new LogicException());

        $listener = new PageViewAnalyticsListener($service);

        $this->expectException(LogicException::class);

        $listener(new TerminateEvent($kernel, $request, $response));
    }
}
