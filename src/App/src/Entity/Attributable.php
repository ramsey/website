<?php

declare(strict_types=1);

namespace App\Entity;

interface Attributable
{
    public function getAttributes(): Attributes;
}
