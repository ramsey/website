<?php

declare(strict_types=1);

namespace AppTest\Handler\Blog;

use App\Handler\Blog\ListHandler;
use App\Repository\PostRepository;
use App\Response\HtmlResponseFactory;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Router\RouterInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Test\Website\TestCase;

class ListHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $postRepository = $this->mockery(PostRepository::class);

        $templateRenderer = $this->mockery(TemplateRendererInterface::class);
        $templateRenderer->expects()->render('app::blog/list')->andReturn('foo');

        $request = $this->mockery(ServerRequestInterface::class);
        $responseFactory = new HtmlResponseFactory($templateRenderer);
        $router = $this->mockery(RouterInterface::class);

        $handler = new ListHandler($postRepository, $templateRenderer, $responseFactory, $router);
        $response = $handler->handle($request);

        $this->assertInstanceOf(HtmlResponse::class, $response);
        $this->assertSame('foo', $response->getBody()->getContents());
    }
}
