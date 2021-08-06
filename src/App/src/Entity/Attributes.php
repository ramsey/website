<?php

declare(strict_types=1);

namespace App\Entity;

use Ramsey\Collection\Map\AbstractMap;
use Ramsey\Collection\Map\MapInterface;

/**
 * Attributes provide a means to add any number of free-form, arbitrary values
 *
 * @extends AbstractMap<mixed>
 * @implements MapInterface<mixed>
 */
class Attributes extends AbstractMap implements MapInterface
{
}
