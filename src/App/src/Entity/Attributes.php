<?php

declare(strict_types=1);

namespace App\Entity;

use ArrayAccess;

/**
 * Attributes provide a means to add any number of free-form, arbitrary values
 *
 * @implements ArrayAccess<string, mixed>
 */
class Attributes implements ArrayAccess
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

    /**
     * @param string $offset
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->has($offset);
    }

    /**
     * @param string $offset
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    /**
     * @param string $offset
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set($offset, $value);
    }

    /**
     * @param string $offset
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->attributes[$offset]);
    }
}
