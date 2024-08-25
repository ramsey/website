<?php

declare(strict_types=1);

use App\Kernel;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Dotenv\Dotenv;

require_once __DIR__ . '/vendor/autoload_runtime.php';

date_default_timezone_set('UTC');

(new Dotenv())->bootEnv(__DIR__ . '/.env');

// phpcs:ignore SlevomatCodingStandard.Variables.DisallowSuperGlobalVariable.DisallowedSuperGlobalVariable
$kernel = new Kernel($_ENV['APP_ENV'], (bool) $_ENV['APP_DEBUG']);
$kernel->boot();

// phpcs:disable SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
$container = $kernel->getContainer();

/** @var Registry $doctrine */
$doctrine = $kernel->getContainer()->get('doctrine');
$entityManager = $doctrine->getManager();
