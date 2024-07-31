<?php

declare(strict_types=1);

namespace App\Tests\Doctrine\Traits;

use App\Doctrine\Traits\Blamable;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class BlamableTest extends TestCase
{
    public function testCreatedBy(): void
    {
        $blamable = new class () {
            use Blamable;
        };

        $user = new User();

        $this->assertSame($blamable, $blamable->setCreatedBy($user));
        $this->assertSame($user, $blamable->getCreatedBy());
    }

    public function testUpdatedBy(): void
    {
        $blamable = new class () {
            use Blamable;
        };

        $user = new User();

        $this->assertSame($blamable, $blamable->setUpdatedBy($user));
        $this->assertSame($user, $blamable->getUpdatedBy());
    }
}
