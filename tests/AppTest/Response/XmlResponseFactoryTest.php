<?php

declare(strict_types=1);

namespace AppTest\Response;

use App\Response\AtomResponse;
use App\Response\XmlResponseFactory;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\StreamInterface;
use Ramsey\Test\Website\TestCase;

class XmlResponseFactoryTest extends TestCase
{
    public function testAtomResponse(): void
    {
        $responseFactory = new XmlResponseFactory();
        $stream = $this->mockery(StreamInterface::class);

        $this->assertInstanceOf(AtomResponse::class, $responseFactory->atomResponse($stream));
    }

    public function testRedirect(): void
    {
        $responseFactory = new XmlResponseFactory();
        $response = $responseFactory->redirect(
            uri: 'https://example.com/redirect',
        );

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(302, $response->getStatusCode());
        $this->assertCount(1, $response->getHeader('Location'));
        $this->assertSame('https://example.com/redirect', $response->getHeader('Location')[0]);
    }
}
