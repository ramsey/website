<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\OpenPgpWebKey;
use PHPUnit\Framework\TestCase;

class OpenPgpWebKeyTest extends TestCase
{
    public function testGetRawBinaryKey(): void
    {
        $key = new OpenPgpWebKey('example.com', 'localpart', 'Zm9v');

        $this->assertSame('foo', $key->getRawBinaryKey());
    }
}
