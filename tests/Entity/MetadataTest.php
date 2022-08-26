<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Metadata;
use App\Tests\TestCase;

class MetadataTest extends TestCase
{
    public function testAcceptsArbitraryKeyValuePairs(): void
    {
        $metadata = new Metadata();

        $metadata['foo'] = 'bar';
        $metadata['baz'] = 'quux';

        $this->assertCount(2, $metadata);
        $this->assertSame('bar', $metadata['foo']);
        $this->assertSame('quux', $metadata['baz']);
    }
}
