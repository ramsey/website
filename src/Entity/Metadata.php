<?php

declare(strict_types=1);

namespace App\Entity;

use Ramsey\Collection\Map\AbstractMap;
use Ramsey\Collection\Map\MapInterface;

/**
 * Metadata provides a means to add any number of free-form, arbitrary values
 *
 * @extends AbstractMap<mixed>
 * @implements MapInterface<mixed>
 */
final class Metadata extends AbstractMap implements MapInterface
{
}
