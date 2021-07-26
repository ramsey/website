<?php

declare(strict_types=1);

use Laminas\ServiceManager\ServiceManager;

// Load configuration
$config = require __DIR__ . '/config.php';

/** @var array{services: array{config: mixed[]}} $dependencies */
$dependencies = $config['dependencies'];
$dependencies['services']['config'] = $config;

// Build container
return new ServiceManager($dependencies);
