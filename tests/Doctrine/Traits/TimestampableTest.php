<?php

declare(strict_types=1);

namespace App\Tests\Doctrine\Traits;

use App\Doctrine\Traits\Timestampable;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class TimestampableTest extends TestCase
{
    public function testCreatedAtWithNoParam(): void
    {
        $timestampable = new class {
            use Timestampable;
        };

        $this->assertSame($timestampable, $timestampable->setCreatedAt());
        $this->assertInstanceOf(DateTimeImmutable::class, $timestampable->getCreatedAt());
    }

    public function testCreatedAtSetsDate(): void
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

    public function testCreatedAtDoesNotOverwrite(): void
    {
        $timestampable = new class {
            use Timestampable;
        };

        $date1 = new DateTimeImmutable('-3 weeks');
        $date2 = new DateTimeImmutable('-2 weeks');

        $this->assertSame($timestampable, $timestampable->setCreatedAt($date1));

        // Try setting $date2.
        $this->assertSame($timestampable, $timestampable->setCreatedAt($date2));

        // createdAt is still $date1.
        $this->assertEquals($date1, $timestampable->getCreatedAt());
    }

    public function testUpdatedAtWithNoParam(): void
    {
        $timestampable = new class {
            use Timestampable;
        };

        $this->assertSame($timestampable, $timestampable->setUpdatedAt());
        $this->assertInstanceOf(DateTimeImmutable::class, $timestampable->getUpdatedAt());
    }

    public function testUpdatedAtSetsDate(): void
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

    public function testUpdatedAtDoesOverwrite(): void
    {
        $timestampable = new class {
            use Timestampable;
        };

        $date1 = new DateTimeImmutable('-3 weeks');
        $date2 = new DateTimeImmutable('-2 weeks');

        $this->assertSame($timestampable, $timestampable->setUpdatedAt($date1));

        // Try setting $date2.
        $this->assertSame($timestampable, $timestampable->setUpdatedAt($date2));

        // updatedAt is now $date2.
        $this->assertEquals($date2, $timestampable->getUpdatedAt());
    }
}
