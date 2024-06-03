<?php

declare(strict_types=1);

namespace App\Tests\EventListener;

use App\Controller\ShortUrlController;
use App\EventListener\UrlShortenerListener;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class UrlShortenerListenerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[TestDox("requests that aren't to bram.se or /su/ path through the listener")]
    public function testUrlShortenerListenerDoesNothing(): void
    {
        $parameterBag = Mockery::mock(ParameterBag::class);
        $parameterBag->expects('set')->never();

        $request = Mockery::mock(Request::class);
        $request->expects('getHost')->andReturn('ben.ramsey.dev');
        $request->expects('getRequestUri')->andReturn('/path/to/foobar');
        $request->attributes = $parameterBag;

        $event = Mockery::mock(RequestEvent::class);
        $event->expects('getRequest')->twice()->andReturn($request);

        $listener = new UrlShortenerListener();
        $listener($event);
    }

    #[TestDox('requests to bram.se are redirected to ShortUrlController')]
    public function testUrlShortenerListenerForBramseHostname(): void
    {
        $parameterBag = Mockery::mock(ParameterBag::class);
        $parameterBag->expects('set')->with('_controller', ShortUrlController::class);

        $request = Mockery::mock(Request::class);
        $request->expects('getHost')->andReturn('bram.se');
        $request->expects('getRequestUri')->andReturn('/path/to/foobar');
        $request->attributes = $parameterBag;

        $event = Mockery::mock(RequestEvent::class);
        $event->expects('getRequest')->times(3)->andReturn($request);

        $listener = new UrlShortenerListener();
        $listener($event);
    }

    #[TestDox('requests to /su/ are redirected to ShortUrlController')]
    public function testUrlShortenerListenerForSuPath(): void
    {
        $parameterBag = Mockery::mock(ParameterBag::class);
        $parameterBag->expects('set')->with('_controller', ShortUrlController::class);

        $request = Mockery::mock(Request::class);
        $request->expects('getHost')->andReturn('ben.ramsey.dev');
        $request->expects('getRequestUri')->andReturn('/su/foobar');
        $request->attributes = $parameterBag;

        $event = Mockery::mock(RequestEvent::class);
        $event->expects('getRequest')->times(3)->andReturn($request);

        $listener = new UrlShortenerListener();
        $listener($event);
    }
}
