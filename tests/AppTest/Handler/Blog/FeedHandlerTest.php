<?php

declare(strict_types=1);

namespace AppTest\Handler\Blog;

use App\Handler\Blog\FeedHandler;
use App\Repository\PostRepository;
use App\Response\AtomResponse;
use App\Response\XmlResponseFactory;
use Mezzio\Router\RouterInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Test\Website\TestCase;

class FeedHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $postRepository = $this->mockery(PostRepository::class);

        $templateRenderer = $this->mockery(TemplateRendererInterface::class);
        $templateRenderer->expects()->render('app::blog/feed.xml.twig')->andReturn('foo');

        $request = $this->mockery(ServerRequestInterface::class);
        $responseFactory = new XmlResponseFactory();
        $router = $this->mockery(RouterInterface::class);

        $handler = new FeedHandler($postRepository, $templateRenderer, $responseFactory, $router);
        $response = $handler->handle($request);

        $this->assertInstanceOf(AtomResponse::class, $response);
        $this->assertSame('foo', $response->getBody()->getContents());
    }
}
