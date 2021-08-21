<?php

declare(strict_types=1);

namespace AppTest\Response;

use App\Response\AtomResponse;
use Psr\Http\Message\StreamInterface;
use Ramsey\Test\Website\TestCase;

class AtomResponseTest extends TestCase
{
    public function testConstructorUsesAtomContentType(): void
    {
        $stream = $this->mockery(StreamInterface::class);
        $response = new AtomResponse($stream);

        $this->assertCount(1, $response->getHeader('Content-Type'));
        $this->assertSame(
            'application/atom+xml; charset=utf-8',
            $response->getHeader('Content-Type')[0],
        );
    }
}
