<?php

declare(strict_types=1);

namespace App\Tests\EventListener;

use App\Controller\UnknownUriController;
use App\EventListener\IndexPhpListener;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class IndexPhpListenerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[TestDox("requests that don't start with /index.php pass through")]
    public function testListenerDoesNothing(): void
    {
        $parameterBag = Mockery::mock(ParameterBag::class);
        $parameterBag->expects('set')->never();

        $request = Mockery::mock(Request::class);
        $request->expects('getRequestUri')->andReturn('/path/to/foobar');
        $request->attributes = $parameterBag;

        $event = Mockery::mock(RequestEvent::class);
        $event->expects('getRequest')->andReturn($request);

        $listener = new IndexPhpListener();
        $listener($event);
    }

    #[TestDox('requests that start with /index.php get sent to the UnknownUriController')]
    public function testListenerRewritesIndexPhpToUnknownUriController(): void
    {
        $parameterBag = Mockery::mock(ParameterBag::class);
        $parameterBag->expects('set')->with('_controller', UnknownUriController::class);

        $request = Mockery::mock(Request::class);
        $request->expects('getRequestUri')->andReturn('/INdEX.pHp/path/to/foobar');
        $request->attributes = $parameterBag;

        $event = Mockery::mock(RequestEvent::class);
        $event->expects('getRequest')->twice()->andReturn($request);

        $listener = new IndexPhpListener();
        $listener($event);
    }
}
