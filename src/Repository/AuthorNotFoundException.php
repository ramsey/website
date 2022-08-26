<?php

declare(strict_types=1);

namespace App\Repository;

use App\AppException;
use RuntimeException;

final class AuthorNotFoundException extends RuntimeException implements AppException
{
}
