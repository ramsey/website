<?php

declare(strict_types=1);

namespace AppTest\Handler;

use App\Entity\Page;
use App\Handler\PageHandler;
use App\Repository\PageRepository;
use App\Response\HtmlResponseFactory;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Test\Website\TestCase;

class PageHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $slug = $this->faker()->slug;

        /** @var string $content */
        $content = $this->faker()->paragraphs(3, true);

        $page = new Page(
            title: $this->faker()->sentence,
            content: $content,
        );

        $pageRepository = $this->mockery(PageRepository::class);
        $pageRepository
            ->expects()
            ->findByAttributes(['slug' => $slug])
            ->andReturn($page);

        $templateRenderer = $this->mockery(TemplateRendererInterface::class);
        $templateRenderer
            ->expects()
            ->render('app::page', [
                'title' => $page->getTitle(),
                'content' => $page->getContent(),
            ])
            ->andReturn('foo');

        $request = $this->mockery(ServerRequestInterface::class);
        $request->expects()->getAttribute('slug')->andReturn($slug);

        $responseFactory = new HtmlResponseFactory($templateRenderer);

        $pageHandler = new PageHandler($pageRepository, $templateRenderer, $responseFactory);
        $response = $pageHandler->handle($request);

        $this->assertInstanceOf(HtmlResponse::class, $response);
        $this->assertSame('foo', $response->getBody()->getContents());
    }

    public function testHandleReturnsNotFound(): void
    {
        $slug = $this->faker()->slug;

        $pageRepository = $this->mockery(PageRepository::class);
        $pageRepository
            ->expects()
            ->findByAttributes(['slug' => $slug])
            ->andReturnNull();

        $templateRenderer = $this->mockery(TemplateRendererInterface::class);
        $templateRenderer->expects()->render('error::404')->andReturn('not found');

        $request = $this->mockery(ServerRequestInterface::class);
        $request->expects()->getAttribute('slug')->andReturn($slug);

        $responseFactory = new HtmlResponseFactory($templateRenderer);

        $pageHandler = new PageHandler($pageRepository, $templateRenderer, $responseFactory);
        $response = $pageHandler->handle($request);

        $this->assertInstanceOf(HtmlResponse::class, $response);
        $this->assertSame(404, $response->getStatusCode());
    }
}
