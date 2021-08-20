<?php

declare(strict_types=1);

namespace AppTest\Handler\Blog;

use App\Entity\Post;
use App\Handler\Blog\PostHandler;
use App\Repository\PostRepository;
use App\Response\HtmlResponseFactory;
use DateTimeImmutable;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Diactoros\Uri;
use Mezzio\Router\RouterInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Test\Website\TestCase;

class PostHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $slug = $this->faker()->slug;

        /** @var string $content */
        $content = $this->faker()->paragraphs(3, true);

        $post = new Post(
            title: $this->faker()->sentence,
            content: $content,
            publishDate: new DateTimeImmutable(),
        );

        $postRepository = $this->mockery(PostRepository::class);
        $postRepository
            ->expects()
            ->findByAttributes(['year' => 2021, 'slug' => $slug])
            ->andReturn($post);

        $templateRenderer = $this->mockery(TemplateRendererInterface::class);
        $templateRenderer
            ->expects()
            ->render('app::blog/post', [
                'title' => $post->getTitle(),
                'content' => $post->getContent(),
            ])
            ->andReturn('foo');

        $request = $this->mockery(ServerRequestInterface::class);
        $request->expects()->getAttribute('month')->andReturnNull();
        $request->expects()->getAttribute('year')->andReturn('2021');
        $request->expects()->getAttribute('slug')->andReturn($slug);

        $responseFactory = new HtmlResponseFactory($templateRenderer);
        $router = $this->mockery(RouterInterface::class);

        $blogHandler = new PostHandler($postRepository, $templateRenderer, $responseFactory, $router);
        $response = $blogHandler->handle($request);

        $this->assertInstanceOf(HtmlResponse::class, $response);
        $this->assertSame('foo', $response->getBody()->getContents());
    }

    public function testHandleReturnsNotFound(): void
    {
        $slug = $this->faker()->slug;

        $postRepository = $this->mockery(PostRepository::class);
        $postRepository
            ->expects()
            ->findByAttributes(['year' => 2021, 'slug' => $slug])
            ->andReturnNull();

        $templateRenderer = $this->mockery(TemplateRendererInterface::class);
        $templateRenderer->expects()->render('error::404')->andReturn('not found');

        $request = $this->mockery(ServerRequestInterface::class);
        $request->expects()->getAttribute('month')->andReturnNull();
        $request->expects()->getAttribute('year')->andReturn('2021');
        $request->expects()->getAttribute('slug')->andReturn($slug);

        $responseFactory = new HtmlResponseFactory($templateRenderer);
        $router = $this->mockery(RouterInterface::class);

        $blogHandler = new PostHandler($postRepository, $templateRenderer, $responseFactory, $router);
        $response = $blogHandler->handle($request);

        $this->assertInstanceOf(HtmlResponse::class, $response);
        $this->assertSame(404, $response->getStatusCode());
    }

    public function testHandleWithMonthRedirects(): void
    {
        $slug = $this->faker()->slug;

        /** @var string $content */
        $content = $this->faker()->paragraphs(3, true);

        $post = new Post(
            title: $this->faker()->sentence,
            content: $content,
            publishDate: new DateTimeImmutable(),
        );

        $postRepository = $this->mockery(PostRepository::class);
        $postRepository
            ->expects()
            ->findByAttributes(['year' => 2021, 'month' => 7, 'slug' => $slug])
            ->andReturn($post);

        $templateRenderer = $this->mockery(TemplateRendererInterface::class);

        $request = $this->mockery(ServerRequestInterface::class);
        $request->expects()->getAttribute('month')->andReturn('07');
        $request->expects()->getAttribute('year')->andReturn('2021');
        $request->expects()->getAttribute('slug')->andReturn($slug);
        $request->expects()->getUri()->andReturn(new Uri('https://example.com/foo'));

        $responseFactory = new HtmlResponseFactory($templateRenderer);

        $router = $this->mockery(RouterInterface::class);
        $router
            ->expects()
            ->generateUri('blog.post', ['year' => 2021, 'month' => 7, 'slug' => $slug])
            ->andReturn('/blog/2021/' . $slug);

        $blogHandler = new PostHandler($postRepository, $templateRenderer, $responseFactory, $router);
        $response = $blogHandler->handle($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(301, $response->getStatusCode());
        $this->assertCount(1, $response->getHeader('Location'));
        $this->assertSame('https://example.com/blog/2021/' . $slug, $response->getHeader('Location')[0]);
    }
}
