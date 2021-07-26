<?php

declare(strict_types=1);

namespace AppTest\Handler;

use App\Handler\PingHandler;
use Laminas\Diactoros\Response\JsonResponse;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Test\Website\TestCase;

use function json_decode;

class PingHandlerTest extends TestCase
{
    use ProphecyTrait;

    public function testResponse(): void
    {
        /** @var ServerRequestInterface & ObjectProphecy $serverRequest */
        $serverRequest = $this->prophesize(ServerRequestInterface::class)->reveal();

        $pingHandler = new PingHandler();
        $response = $pingHandler->handle($serverRequest);

        $json = json_decode((string) $response->getBody());

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertTrue(isset($json->ack));
    }
}
