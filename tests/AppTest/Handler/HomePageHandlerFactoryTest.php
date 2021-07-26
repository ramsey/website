<?php

declare(strict_types=1);

namespace AppTest\Handler;

use App\Handler\HomePageHandler;
use App\Handler\HomePageHandlerFactory;
use Mezzio\Router\RouterInterface;
use Mezzio\Template\TemplateRendererInterface;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Ramsey\Test\Website\TestCase;

class HomePageHandlerFactoryTest extends TestCase
{
    use ProphecyTrait;

    protected ObjectProphecy $container;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $router = $this->prophesize(RouterInterface::class);

        // @phpstan-ignore-next-line
        $this->container->get(RouterInterface::class)->willReturn($router);
    }

    public function testFactoryWithoutTemplate(): void
    {
        $factory = new HomePageHandlerFactory();

        // @phpstan-ignore-next-line
        $this->container->has(TemplateRendererInterface::class)->willReturn(false);

        self::assertInstanceOf(HomePageHandlerFactory::class, $factory);

        /** @var ContainerInterface $container */
        $container = $this->container->reveal();
        $homePage = $factory($container);

        self::assertInstanceOf(HomePageHandler::class, $homePage);
    }

    public function testFactoryWithTemplate(): void
    {
        // @phpstan-ignore-next-line
        $this->container->has(TemplateRendererInterface::class)->willReturn(true);

        // @phpstan-ignore-next-line
        $this->container
            ->get(TemplateRendererInterface::class)
            ->willReturn($this->prophesize(TemplateRendererInterface::class));

        $factory = new HomePageHandlerFactory();

        /** @var ContainerInterface $container */
        $container = $this->container->reveal();
        $homePage = $factory($container);

        self::assertInstanceOf(HomePageHandler::class, $homePage);
    }
}
