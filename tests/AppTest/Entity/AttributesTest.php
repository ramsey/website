<?php

declare(strict_types=1);

namespace AppTest\Entity;

use App\Entity\Attributes;
use Ramsey\Test\Website\TestCase;

class AttributesTest extends TestCase
{
    private Attributes $attributes;

    /**
     * @var array<string, mixed>
     */
    private array $values;

    protected function setUp(): void
    {
        $this->values = [
            'title' => $this->faker()->sentence,
            'publishDate' => $this->faker()->date,
            'lastUpdateDate' => $this->faker()->date,
            'postImage' => $this->faker()->imageUrl,
            'tags' => $this->faker()->words,
        ];

        $this->attributes = new Attributes($this->values);
    }

    /**
     * @dataProvider hasProvider
     */
    public function testHas(string $name, bool $expected): void
    {
        $this->assertSame($expected, $this->attributes->has($name));
    }

    /**
     * @dataProvider hasProvider
     */
    public function testHasArrayAccessVariant(string $name, bool $expected): void
    {
        $this->assertSame($expected, isset($this->attributes[$name]));
    }

    /**
     * @return array<array{0: string, 1: bool}>
     */
    public function hasProvider(): array
    {
        return [
            ['title', true],
            ['publishDate', true],
            ['lastUpdateDate', true],
            ['postImage', true],
            ['tags', true],
            ['foo', false],
            ['bar', false],
        ];
    }

    /**
     * @dataProvider getWithExistingValuesProvider
     */
    public function testGetWithExistingValues(string $name): void
    {
        $this->assertSame($this->values[$name], $this->attributes->get($name));
    }

    /**
     * @dataProvider getWithExistingValuesProvider
     */
    public function testGetWithExistingValuesArrayAccessVariant(string $name): void
    {
        $this->assertSame($this->values[$name], $this->attributes[$name]);
    }

    /**
     * @return array<string[]>
     */
    public function getWithExistingValuesProvider(): array
    {
        return [
            ['title'],
            ['publishDate'],
            ['lastUpdateDate'],
            ['postImage'],
            ['tags'],
        ];
    }

    public function testGetWithNonexistentValues(): void
    {
        $this->assertNull($this->attributes->get('foo'));
    }

    public function testGetWithNonexistentValuesArrayAccessVariant(): void
    {
        $this->assertNull($this->attributes['foo']);
    }

    public function testGetWithDefaultValue(): void
    {
        $this->assertSame('Hello!', $this->attributes->get('foo', 'Hello!'));
    }

    public function testSet(): void
    {
        $this->attributes->set('foo', 'Goodbye!');

        $this->assertSame('Goodbye!', $this->attributes->get('foo'));
    }

    public function testSetArrayAccessVariant(): void
    {
        $this->attributes['foo'] = 'Goodbye!';

        $this->assertSame('Goodbye!', $this->attributes['foo']);
    }

    public function testUnset(): void
    {
        unset($this->attributes['title']);

        $this->assertFalse($this->attributes->has('title'));
    }
}
