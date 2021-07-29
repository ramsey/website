<?php

declare(strict_types=1);

namespace AppTest\Entity;

use App\Entity\Attributes;
use Faker\Factory;
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
        $faker = Factory::create();

        $this->values = [
            'title' => $faker->sentence,
            'publishDate' => $faker->date,
            'lastUpdateDate' => $faker->date,
            'postImage' => $faker->imageUrl,
            'tags' => $faker->words,
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

    public function testGetWithDefaultValue(): void
    {
        $this->assertSame('Hello!', $this->attributes->get('foo', 'Hello!'));
    }

    public function testSet(): void
    {
        $this->attributes->set('foo', 'Goodbye!');

        $this->assertSame('Goodbye!', $this->attributes->get('foo'));
    }
}
