<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\KeybaseController;
use Mockery;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;

class KeybaseControllerTest extends WebTestCase
{
    #[TestDox('Request to /.well-known/keybase.txt responds with 404 for unknown host')]
    public function testKeybaseResponseWhenNotFound(): void
    {
        $client = static::createClient();
        $client->request('GET', '/.well-known/keybase.txt');

        $this->assertResponseStatusCodeSame(404);
        $this->assertResponseHeaderSame(
            'cache-control',
            'max-age=604800, public, stale-while-revalidate=86400',
        );
    }

    #[TestDox('Request to /.well-known/keybase.txt responds successfully for ben.ramsey.dev')]
    public function testKeybaseResponseForBenDotRamseyDotDev(): void
    {
        $twig = Mockery::mock(Environment::class);
        $twig
            ->expects('render')
            ->with('keybase/ben-ramsey-dev.txt.twig')
            ->andReturn('keybase proof for ben.ramsey.dev');

        $request = Request::create('https://ben.ramsey.dev/.well-known/keybase.txt');

        $controller = new KeybaseController($twig);
        $response = $controller($request);

        $this->assertSame('keybase proof for ben.ramsey.dev', $response->getContent());
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('text/plain', $response->headers->get('Content-Type'));
    }

    #[TestDox('Request to /.well-known/keybase.txt responds successfully for benramsey.com')]
    public function testKeybaseResponseForBenRamseyDotCom(): void
    {
        $twig = Mockery::mock(Environment::class);
        $twig
            ->expects('render')
            ->with('keybase/benramsey-com.txt.twig')
            ->andReturn('keybase proof for benramsey.com');

        $request = Request::create('https://benramsey.com/.well-known/keybase.txt');

        $controller = new KeybaseController($twig);
        $response = $controller($request);

        $this->assertSame('keybase proof for benramsey.com', $response->getContent());
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('text/plain', $response->headers->get('Content-Type'));
    }
}
