<?php

declare(strict_types=1);

namespace AppTest\Handler;

use App\Entity\Post;
use App\Handler\BlogHandler;
use App\Repository\PostRepository;
use App\Response\NotFoundResponse;
use DateTimeImmutable;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Test\Website\TestCase;

class BlogHandlerTest extends TestCase
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
        $postRepository->expects()->find(2021, 7, $slug)->andReturn($post);

        $templateRenderer = $this->mockery(TemplateRendererInterface::class);
        $templateRenderer
            ->expects()
            ->render(
                'app::blog',
                [
                    'title' => $post->getTitle(),
                    'content' => $post->getContent(),
                ],
            )
            ->andReturn('foo');

        $request = $this->mockery(ServerRequestInterface::class);
        $request->expects()->getAttribute('month')->andReturn('07');
        $request->expects()->getAttribute('year')->andReturn('2021');
        $request->expects()->getAttribute('slug')->andReturn($slug);

        $blogHandler = new BlogHandler($postRepository, $templateRenderer);
        $response = $blogHandler->handle($request);

        $this->assertInstanceOf(HtmlResponse::class, $response);
        $this->assertSame('foo', $response->getBody()->getContents());
    }

    public function testHandleReturnsNotFound(): void
    {
        $slug = $this->faker()->slug;

        $postRepository = $this->mockery(PostRepository::class);
        $postRepository->expects()->find(2021, 7, $slug)->andReturnNull();

        $templateRenderer = $this->mockery(TemplateRendererInterface::class);

        $request = $this->mockery(ServerRequestInterface::class);
        $request->expects()->getAttribute('month')->andReturn('07');
        $request->expects()->getAttribute('year')->andReturn('2021');
        $request->expects()->getAttribute('slug')->andReturn($slug);

        $blogHandler = new BlogHandler($postRepository, $templateRenderer);
        $response = $blogHandler->handle($request);

        $this->assertInstanceOf(NotFoundResponse::class, $response);
        $this->assertSame(404, $response->getStatusCode());
    }
}
