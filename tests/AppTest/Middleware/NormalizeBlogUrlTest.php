<?php

declare(strict_types=1);

namespace AppTest\Middleware;

use App\Middleware\NormalizeBlogUrl;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Diactoros\Uri;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Test\Website\TestCase;

class NormalizeBlogUrlTest extends TestCase
{
    public function testProcessAddsZeroCharacterToUrl(): void
    {
        $slug = $this->faker()->slug;
        $originalUrl = 'https://example.com/blog/2021/7/' . $slug . '/';
        $expectedUrl = 'https://example.com/blog/2021/07/' . $slug . '/';

        $request = $this->mockery(ServerRequestInterface::class);
        $request->expects()->getAttribute('month')->andReturn('7');
        $request->expects()->getAttribute('year')->andReturn('2021');
        $request->expects()->getAttribute('slug')->andReturn($slug);
        $request->expects()->getUri()->andReturn(new Uri($originalUrl));

        $handler = $this->mockery(RequestHandlerInterface::class);

        $middleware = new NormalizeBlogUrl();
        $response = $middleware->process($request, $handler);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(301, $response->getStatusCode());
        $this->assertCount(1, $response->getHeader('Location'));
        $this->assertSame($expectedUrl, $response->getHeader('Location')[0]);
    }

    public function testProcessWhenUrlIsAlreadyFormattedProperly(): void
    {
        $slug = $this->faker()->slug;
        $url = 'https://example.com/blog/2021/07/' . $slug . '/';

        $request = $this->mockery(ServerRequestInterface::class);
        $request->expects()->getAttribute('month')->andReturn('07');
        $request->expects()->getUri()->andReturn(new Uri($url));

        $handler = $this->mockery(RequestHandlerInterface::class);
        $handler->expects()->handle($request)->andReturn(new Response());

        $middleware = new NormalizeBlogUrl();
        $response = $middleware->process($request, $handler);

        $this->assertNotInstanceOf(RedirectResponse::class, $response);
        $this->assertNotEquals(301, $response->getStatusCode());
    }
}
