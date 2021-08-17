<?php

declare(strict_types=1);

namespace AppTest\Response;

use App\Response\HtmlResponseFactory;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Template\TemplateRendererInterface;
use Mockery\MockInterface;
use Ramsey\Test\Website\TestCase;

class HtmlResponseFactoryTest extends TestCase
{
    private HtmlResponseFactory $responseFactory;

    /** @var TemplateRendererInterface&MockInterface */
    private TemplateRendererInterface $template;

    protected function setUp(): void
    {
        parent::setUp();

        $this->template = $this->mockery(TemplateRendererInterface::class);
        $this->responseFactory = new HtmlResponseFactory($this->template);
    }

    public function testResponse(): void
    {
        $response = $this->responseFactory->response(
            content: 'Hello!',
            status: 201,
            headers: [
                'Location' => 'https://example.com/foo',
                'X-Foo' => 'foobar',
            ],
        );

        $this->assertInstanceOf(HtmlResponse::class, $response);
        $this->assertSame('Hello!', $response->getBody()->getContents());
        $this->assertSame(201, $response->getStatusCode());
        $this->assertCount(1, $response->getHeader('Location'));
        $this->assertSame('https://example.com/foo', $response->getHeader('Location')[0]);
        $this->assertCount(1, $response->getHeader('X-Foo'));
        $this->assertSame('foobar', $response->getHeader('X-Foo')[0]);
    }

    public function testRedirect(): void
    {
        $response = $this->responseFactory->redirect(
            uri: 'https://example.com/redirect',
            status: 303,
            headers: ['X-Bar' => 'bar baz'],
        );

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(303, $response->getStatusCode());
        $this->assertCount(1, $response->getHeader('Location'));
        $this->assertSame('https://example.com/redirect', $response->getHeader('Location')[0]);
        $this->assertCount(1, $response->getHeader('X-Bar'));
        $this->assertSame('bar baz', $response->getHeader('X-Bar')[0]);
    }

    public function testNotFound(): void
    {
        $this->template->expects()->render('error::404')->andReturn('not found');

        $response = $this->responseFactory->notFound(
            headers: [
                'X-Foo' => 'foo bar baz',
            ],
        );

        $this->assertInstanceOf(HtmlResponse::class, $response);
        $this->assertSame('not found', $response->getBody()->getContents());
        $this->assertSame(404, $response->getStatusCode());
        $this->assertCount(1, $response->getHeader('X-Foo'));
        $this->assertSame('foo bar baz', $response->getHeader('X-Foo')[0]);
    }
}
