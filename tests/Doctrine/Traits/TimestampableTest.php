<?php

declare(strict_types=1);

namespace App\Tests\Doctrine\Traits;

use App\Doctrine\Traits\Timestampable;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class TimestampableTest extends TestCase
{
    public function testCreatedAt(): void
    {
        $timestampable = new class {
            use Timestampable;
        };

        $date = new DateTimeImmutable();

        $this->assertSame($timestampable, $timestampable->setCreatedAt($date));

        // They aren't the same instance because we use
        // DateTimeImmutable::createFromInterface() when setting it.
        $this->assertEquals($date, $timestampable->getCreatedAt());
        $this->assertNotSame($date, $timestampable->getCreatedAt());
    }

    public function testUpdatedAt(): void
    {
        $timestampable = new class {
            use Timestampable;
        };

        $date = new DateTimeImmutable();

        $this->assertNull($timestampable->getUpdatedAt());
        $this->assertSame($timestampable, $timestampable->setUpdatedAt($date));

        // They aren't the same instance because we use
        // DateTimeImmutable::createFromInterface() when setting it.
        $this->assertEquals($date, $timestampable->getUpdatedAt());
        $this->assertNotSame($date, $timestampable->getUpdatedAt());
    }
}
