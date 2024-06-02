<?php

declare(strict_types=1);

namespace App\Tests\Service\Codec;

use App\Service\Codec\Base62Codec;
use PHPUnit\Framework\TestCase;

final class Base62CodecTest extends TestCase
{
    public function testEncode(): void
    {
        $codec = new Base62Codec();

        $this->assertSame('UiP9AV6Y', $codec->encode('base62'));
    }

    public function testDecode(): void
    {
        $codec = new Base62Codec();

        $this->assertSame('base62', $codec->decode('UiP9AV6Y'));
    }
}
