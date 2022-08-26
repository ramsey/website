<?php

declare(strict_types=1);

namespace App\Repository;

use App\AppException;
use RuntimeException;

final class MultipleMatchesException extends RuntimeException implements AppException
{
}
