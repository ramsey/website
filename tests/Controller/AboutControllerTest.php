<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\AboutController;
use Mockery;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;

use function md5;
use function sprintf;

class AboutControllerTest extends WebTestCase
{
    #[Group('functional')]
    #[TestDox('Request to /about page returns successful response')]
    public function testAboutPageRequest(): void
    {
        $client = static::createClient();
        $client->request('GET', '/about');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'About Me');
    }

    #[Group('unit')]
    #[TestDox('About page response is 200 and includes ETag header')]
    public function testAboutPageResponseHasEtag(): void
    {
        $contents = '<h1>Hello World</h1>';
        $etag = sprintf('"%s"', md5($contents));

        $request = Request::create('/about');

        $twig = Mockery::mock(Environment::class);
        $twig->expects('render')
            ->once()
            ->with('about.html.twig')
            ->andReturn($contents);

        $controller = new AboutController($twig);
        $response = $controller($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($etag, $response->getEtag());
    }

    #[Group('unit')]
    #[TestDox('About page response is 304 when If-None-Match matches ETag')]
    public function testAboutPageResponseIsNotModified(): void
    {
        $contents = '<h1>Hello World</h1>';
        $etag = sprintf('"%s"', md5($contents));

        $request = Request::create('/about');
        $request->headers->set('If-None-Match', $etag);

        $twig = Mockery::mock(Environment::class);
        $twig->expects('render')
            ->once()
            ->with('about.html.twig')
            ->andReturn($contents);

        $controller = new AboutController($twig);
        $response = $controller($request);

        $this->assertSame(304, $response->getStatusCode());
    }

    #[Group('unit')]
    #[TestDox('About page response is 200 when If-Not-Match does not match ETag')]
    public function testAboutPageResponseWhenEtagDoesNotMatch(): void
    {
        $contents = '<h1>Hello World</h1>';
        $etag = sprintf('"%s"', md5('Not matching content'));

        $request = Request::create('/about');
        $request->headers->set('If-None-Match', $etag);

        $twig = Mockery::mock(Environment::class);
        $twig->expects('render')
            ->once()
            ->with('about.html.twig')
            ->andReturn($contents);

        $controller = new AboutController($twig);
        $response = $controller($request);

        $this->assertSame(200, $response->getStatusCode());
    }
}
