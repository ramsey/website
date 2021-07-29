<?php

declare(strict_types=1);

namespace App\Entity;

/**
 * Website pages and blog posts may have any number of arbitrary, free-form
 * attributes applied to them
 */
class Attributes
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(private array $attributes)
    {
    }

    public function has(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    public function get(string $name, mixed $default = null): mixed
    {
        return $this->attributes[$name] ?? $default;
    }

    public function set(string $name, mixed $value): void
    {
        $this->attributes[$name] = $value;
    }
}
