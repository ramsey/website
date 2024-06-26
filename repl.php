<?php

declare(strict_types=1);

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;

require_once __DIR__ . '/vendor/autoload_runtime.php';

date_default_timezone_set('UTC');

(new Dotenv())->bootEnv(__DIR__ . '/.env');

$kernel = new Kernel('test', true);
$kernel->boot();
