<?php

declare(strict_types=1);

namespace App\Tests\Doctrine\Traits;

use App\Doctrine\Traits\SoftDeleteable;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class SoftDeleteableTest extends TestCase
{
    public function testDeletedAt(): void
    {
        $softDeletable = new class {
            use SoftDeleteable;
        };

        $date = new DateTimeImmutable();

        $this->assertNull($softDeletable->getDeletedAt());
        $this->assertSame($softDeletable, $softDeletable->setDeletedAt($date));

        // They aren't the same instance because we use
        // DateTimeImmutable::createFromInterface() when setting it.
        $this->assertEquals($date, $softDeletable->getDeletedAt());
        $this->assertNotSame($date, $softDeletable->getDeletedAt());
    }
}
