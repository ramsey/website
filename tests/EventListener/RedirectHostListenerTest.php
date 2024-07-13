<?php

declare(strict_types=1);

namespace App\Tests\EventListener;

use App\Controller\ShortUrlController;
use App\EventListener\RedirectHostListener;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Twig\Environment;

use function substr;

class RedirectHostListenerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[TestDox('redirect listener simply returns in dev/test environments')]
    #[TestWith(['dev'])]
    #[TestWith(['test'])]
    public function testRedirectHostListenerInDevTestEnvironments(string $environment): void
    {
        $event = Mockery::mock(RequestEvent::class);
        $event->expects('getRequest->getHost')->never();
        $event->expects('getRequest->getRequestUri')->never();
        $event->expects('setResponse')->never();

        $twig = Mockery::mock(Environment::class);

        $listener = new RedirectHostListener($environment, $twig);
        $listener($event);
    }

    #[TestDox('redirect listener simply returns when on primary host')]
    public function testRedirectHostListenerOnPrimaryHost(): void
    {
        $event = Mockery::mock(RequestEvent::class);
        $event->expects('getRequest->getHost')->andReturn('ben.ramsey.dev');
        $event->expects('getRequest->getRequestUri')->andReturn('/');
        $event->expects('setResponse')->never();

        $twig = Mockery::mock(Environment::class);

        $listener = new RedirectHostListener('prod', $twig);
        $listener($event);
    }

    #[TestDox('redirect listener simply returns for the /health route on any host')]
    public function testRedirectHostListenerOnHealthRoute(): void
    {
        $event = Mockery::mock(RequestEvent::class);
        $event->expects('getRequest->getHost')->andReturn('127.0.0.1');
        $event->expects('getRequest->getRequestUri')->andReturn('/health');
        $event->expects('setResponse')->never();

        $twig = Mockery::mock(Environment::class);

        $listener = new RedirectHostListener('prod', $twig);
        $listener($event);
    }

    #[TestDox('redirect listener simply returns for the /robots.txt route on any host')]
    public function testRedirectHostListenerOnRobotsTxtRoute(): void
    {
        $event = Mockery::mock(RequestEvent::class);
        $event->expects('getRequest->getHost')->andReturn('127.0.0.1');
        $event->expects('getRequest->getRequestUri')->andReturn('/robots.txt');
        $event->expects('setResponse')->never();

        $twig = Mockery::mock(Environment::class);

        $listener = new RedirectHostListener('prod', $twig);
        $listener($event);
    }

    #[TestDox('redirect listener simply returns when on short URL host with correct controller')]
    public function testRedirectHostListenerOnShorUrlHost(): void
    {
        $request = Mockery::mock(Request::class);
        $request->expects('getHost')->andReturn('bram.se');
        $request->expects('getRequestUri')->andReturn('/');

        $attributes = Mockery::mock(ParameterBag::class);
        $attributes->expects('get')->with('_controller')->andReturn(ShortUrlController::class);
        $request->attributes = $attributes;

        $event = Mockery::mock(RequestEvent::class);
        $event->allows('getRequest')->andReturn($request);

        $twig = Mockery::mock(Environment::class);

        $listener = new RedirectHostListener('prod', $twig);
        $listener($event);
    }

    #[TestDox('redirect listener uses a 301 redirect on "www" hosts')]
    #[TestWith(['www.ben.ramsey.dev'])]
    #[TestWith(['www.benramsey.dev'])]
    #[TestWith(['www.benramsey.com'])]
    #[TestWith(['www.ramsey.dev'])]
    public function testRedirectForWww(string $host): void
    {
        $event = Mockery::mock(RequestEvent::class);
        $event->expects('getRequest->getHost')->andReturn($host);
        $event->expects('getRequest->getRequestUri')->andReturn('/path/to?foo=bar');

        /** @phpstan-param Response $response */
        $event->expects('setResponse')->with(Mockery::capture($response));

        $twig = Mockery::mock(Environment::class);

        $listener = new RedirectHostListener('prod', $twig);
        $listener($event);

        $trimmedDomain = substr($host, 4);

        $this->assertSame(
            "https://{$trimmedDomain}/path/to?foo=bar",
            $response->headers->get('location'),
        );
        $this->assertSame(301, $response->getStatusCode());
    }

    #[TestDox('redirect listener allows requests to certain paths for openpgpkey hosts')]
    #[TestWith(['openpgpkey.ramsey.dev', '/.well-known/openpgpkey/ramsey.dev/policy'])]
    #[TestWith(['openpgpkey.ramsey.dev', '/.well-known/openpgpkey/ramsey.dev/hu/foobar'])]
    #[TestWith(['openpgpkey.benramsey.com', '/.well-known/openpgpkey/benramsey.com/policy'])]
    #[TestWith(['openpgpkey.benramsey.com', '/.well-known/openpgpkey/benramsey.com/hu/foobar'])]
    public function testAllowOpenPgpKeyHost(string $host, string $path): void
    {
        $event = Mockery::mock(RequestEvent::class);
        $event->expects('getRequest->getHost')->andReturn($host);
        $event->expects('getRequest->getRequestUri')->andReturn($path);
        $event->expects('setResponse')->never();

        $twig = Mockery::mock(Environment::class);

        $listener = new RedirectHostListener('prod', $twig);
        $listener($event);
    }

    #[TestDox('redirect listener forbids openpgpkey hosts from other paths')]
    #[TestWith(['openpgpkey.ramsey.dev', '/'])]
    #[TestWith(['openpgpkey.ramsey.dev', '/about'])]
    #[TestWith(['openpgpkey.ramsey.dev', '/.well-known/security.txt'])]
    #[TestWith(['openpgpkey.benramsey.com', '/'])]
    #[TestWith(['openpgpkey.benramsey.com', '/about'])]
    #[TestWith(['openpgpkey.benramsey.com', '/.well-known/security.txt'])]
    public function testOpenPgpKeyHostForbiddenForOtherPaths(string $host, string $path): void
    {
        $event = Mockery::mock(RequestEvent::class);
        $event->expects('getRequest->getHost')->andReturn($host);
        $event->expects('getRequest->getRequestUri')->andReturn($path);

        /** @phpstan-param Response $response */
        $event->expects('setResponse')->with(Mockery::capture($response));

        $twig = Mockery::mock(Environment::class);
        $twig->expects('render')->with('error/forbidden.html.twig')->andReturn('');

        $listener = new RedirectHostListener('prod', $twig);
        $listener($event);

        $this->assertSame(403, $response->getStatusCode());
    }

    #[TestDox('redirect listener allows ramsey.dev for certain resources')]
    #[TestWith(['/.well-known/matrix/client'])]
    #[TestWith(['/.well-known/matrix/server'])]
    #[TestWith(['/.well-known/openpgpkey/policy'])]
    #[TestWith(['/.well-known/openpgpkey/hu/foobar'])]
    #[TestWith(['/.well-known/webfinger?resource=acct%3Aben%40ramsey.dev'])]
    public function testIsRamseyDevResourceWithWellKnownUris(string $path): void
    {
        $event = Mockery::mock(RequestEvent::class);
        $event->expects('getRequest->getHost')->andReturn('ramsey.dev');
        $event->expects('getRequest->getRequestUri')->andReturn($path);
        $event->expects('setResponse')->never();

        $twig = Mockery::mock(Environment::class);

        $listener = new RedirectHostListener('prod', $twig);
        $listener($event);
    }

    #[TestDox('redirect listener redirects for _matrix and _synapse resources for ramsey.dev')]
    #[TestWith(['/_matrix?foo'])]
    #[TestWith(['/_matrix/bar'])]
    #[TestWith(['/_synapse?foo'])]
    #[TestWith(['/_synapse/bar'])]
    public function testIsRamseyDevResourceForMatrixResources(string $path): void
    {
        $event = Mockery::mock(RequestEvent::class);
        $event->expects('getRequest->getHost')->andReturn('ramsey.dev');
        $event->expects('getRequest->getRequestUri')->andReturn($path);

        /** @phpstan-param Response $response */
        $event->expects('setResponse')->with(Mockery::capture($response));

        $twig = Mockery::mock(Environment::class);

        $listener = new RedirectHostListener('prod', $twig);
        $listener($event);

        $this->assertSame(
            "https://matrix.ramsey.dev{$path}",
            $response->headers->get('location'),
        );
        $this->assertSame(308, $response->getStatusCode());
    }

    #[TestDox('redirect listener uses 302 Found redirects for all other ramsey.dev resources')]
    #[TestWith(['/.well-known/keybase.txt'])]
    #[TestWith(['/.well-known/security.txt'])]
    #[TestWith(['/about'])]
    #[TestWith(['/copyright'])]
    #[TestWith(['/'])]
    public function testIsRamseyDevUsesFoundRedirectsForAllElse(string $path): void
    {
        $event = Mockery::mock(RequestEvent::class);
        $event->expects('getRequest->getHost')->andReturn('ramsey.dev');
        $event->expects('getRequest->getRequestUri')->andReturn($path);

        /** @phpstan-param Response $response */
        $event->expects('setResponse')->with(Mockery::capture($response));

        $twig = Mockery::mock(Environment::class);

        $listener = new RedirectHostListener('prod', $twig);
        $listener($event);

        $this->assertSame(
            "https://ben.ramsey.dev{$path}",
            $response->headers->get('location'),
        );
        $this->assertSame(302, $response->getStatusCode());
    }

    #[TestDox('redirect listener allows benramsey.com for certain resources')]
    #[TestWith(['/.well-known/keybase.txt'])]
    #[TestWith(['/.well-known/openpgpkey/policy'])]
    #[TestWith(['/.well-known/openpgpkey/hu/foobar'])]
    #[TestWith(['/.well-known/webfinger?resource=acct%3Aben%40benramsey.com'])]
    public function testIsBenRamseyComWithWellKnownUris(string $path): void
    {
        $event = Mockery::mock(RequestEvent::class);
        $event->expects('getRequest->getHost')->andReturn('benramsey.com');
        $event->expects('getRequest->getRequestUri')->andReturn($path);
        $event->expects('setResponse')->never();

        $twig = Mockery::mock(Environment::class);

        $listener = new RedirectHostListener('prod', $twig);
        $listener($event);
    }

    #[TestDox('redirect listener permanently redirects for everything else')]
    #[TestWith(['benramsey.com', '/.well-known/security.txt'])]
    #[TestWith(['benramsey.com', '/.well-known/matrix/client'])]
    #[TestWith(['benramsey.com', '/.well-known/matrix/server'])]
    #[TestWith(['benramsey.com', '/_matrix?foo'])]
    #[TestWith(['benramsey.com', '/_matrix/bar'])]
    #[TestWith(['benramsey.com', '/_synapse?foo'])]
    #[TestWith(['benramsey.com', '/_synapse/bar'])]
    #[TestWith(['benramsey.com', '/'])]
    #[TestWith(['benramsey.com', '/about'])]
    #[TestWith(['benramsey.com', '/copyright'])]
    #[TestWith(['benramsey.dev', '/.well-known/security.txt'])]
    #[TestWith(['benramsey.dev', '/.well-known/matrix/client'])]
    #[TestWith(['benramsey.dev', '/.well-known/matrix/server'])]
    #[TestWith(['benramsey.dev', '/_matrix?foo'])]
    #[TestWith(['benramsey.dev', '/_matrix/bar'])]
    #[TestWith(['benramsey.dev', '/_synapse?foo'])]
    #[TestWith(['benramsey.dev', '/_synapse/bar'])]
    #[TestWith(['benramsey.dev', '/'])]
    #[TestWith(['benramsey.dev', '/about'])]
    #[TestWith(['benramsey.dev', '/copyright'])]
    #[TestWith(['bram.se', '/redirects-when-controller-not-set'])]
    public function testEverythingElsePermanentlyRedirects(string $host, string $path): void
    {
        $request = Mockery::mock(Request::class);
        $request->expects('getHost')->andReturn($host);
        $request->expects('getRequestUri')->andReturn($path);

        $attributes = Mockery::mock(ParameterBag::class);
        $attributes->allows('get')->with('_controller')->andReturn(null);
        $request->attributes = $attributes;

        $event = Mockery::mock(RequestEvent::class);
        $event->allows('getRequest')->andReturn($request);

        /** @phpstan-param Response $response */
        $event->expects('setResponse')->with(Mockery::capture($response));

        $twig = Mockery::mock(Environment::class);

        $listener = new RedirectHostListener('prod', $twig);
        $listener($event);

        $this->assertSame(
            "https://ben.ramsey.dev{$path}",
            $response->headers->get('location'),
        );
        $this->assertSame(301, $response->getStatusCode());
    }
}
