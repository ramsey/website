<?php

declare(strict_types=1);

namespace Ramsey\Test\Website;

use Faker\Factory;
use Faker\Generator;
use Ramsey\Dev\Tools\TestCase as BaseTestCase;

/**
 * A base test case for common test functionality
 */
abstract class TestCase extends BaseTestCase
{
    private Generator $faker;

    protected function faker(): Generator
    {
        if (!isset($this->faker)) {
            $this->faker = Factory::create();
        }

        return $this->faker;
    }
}
