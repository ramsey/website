<?php

declare(strict_types=1);

namespace App\Repository\Exception;

use App\Exception;
use RuntimeException;

class MultipleMatches extends RuntimeException implements Exception
{
}