<?php

declare(strict_types=1);

namespace AppTest\Handler;

use App\Handler\HomeHandler;
use App\Response\HtmlResponseFactory;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Test\Website\TestCase;

class HomeHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $templateRenderer = $this->mockery(TemplateRendererInterface::class);
        $templateRenderer->expects()->render('app::home')->andReturn('foo');

        $request = $this->mockery(ServerRequestInterface::class);
        $responseFactory = new HtmlResponseFactory($templateRenderer);

        $homeHandler = new HomeHandler($templateRenderer, $responseFactory);
        $response = $homeHandler->handle($request);

        $this->assertInstanceOf(HtmlResponse::class, $response);
        $this->assertSame('foo', $response->getBody()->getContents());
    }
}
