<?php

declare(strict_types=1);

namespace AppTest\Response;

use App\Response\NotFoundResponse;
use Ramsey\Test\Website\TestCase;

class NotFoundResponseTest extends TestCase
{
    public function testStatusCode(): void
    {
        $response = new NotFoundResponse();

        $this->assertSame(404, $response->getStatusCode());
    }
}
