<?php

declare(strict_types=1);

namespace AppTest\Handler;

use App\Handler\HomePageHandler;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Router\RouterInterface;
use Mezzio\Template\TemplateRendererInterface;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Test\Website\TestCase;

use function get_class;

class HomePageHandlerTest extends TestCase
{
    use ProphecyTrait;

    protected ObjectProphecy $container;
    protected ObjectProphecy $router;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->router = $this->prophesize(RouterInterface::class);
    }

    public function testReturnsHtmlResponseWhenTemplateRendererProvided(): void
    {
        $renderer = $this->prophesize(TemplateRendererInterface::class);

        // @phpstan-ignore-next-line
        $renderer
            ->render('app::home-page', Argument::type('array'))
            ->willReturn('');

        /** @var RouterInterface $router */
        $router = $this->router->reveal();

        /** @var TemplateRendererInterface $rendererInstance */
        $rendererInstance = $renderer->reveal();

        $homePage = new HomePageHandler(
            get_class($this->container->reveal()),
            $router,
            $rendererInstance,
        );

        /** @var ServerRequestInterface & ObjectProphecy $serverRequest */
        $serverRequest = $this->prophesize(ServerRequestInterface::class)->reveal();

        $response = $homePage->handle($serverRequest);

        self::assertInstanceOf(HtmlResponse::class, $response);
    }
}
