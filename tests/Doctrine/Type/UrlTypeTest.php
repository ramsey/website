<?php

declare(strict_types=1);

namespace App\Tests\Doctrine\Type;

use App\Doctrine\Type\UrlType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Types\Exception\SerializationFailed;
use Doctrine\DBAL\Types\Exception\ValueNotConvertible;
use Laminas\Diactoros\Uri;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;

final class UrlTypeTest extends TestCase
{
    private AbstractPlatform $platform;
    private UrlType $type;

    protected function setUp(): void
    {
        $this->platform = new PostgreSQLPlatform();
        $this->type = new UrlType();
    }

    #[TestDox('getSqlDeclaration() returns expected "VARCHAR(255)" string for PostgreSQL')]
    public function testGetSqlDeclaration(): void
    {
        $this->assertSame('VARCHAR(255)', $this->type->getSqlDeclaration([], $this->platform));
    }

    #[TestDox('convertToDatabaseValue() converts value to string or null')]
    #[TestWith([new Uri('https://example.com/foo'), 'https://example.com/foo'])]
    #[TestWith([null, null])]
    #[TestWith(['', null])]
    #[TestWith(['https://my.example.com/bar', 'https://my.example.com/bar'])]
    public function testConvertToDatabaseValue(mixed $value, string | null $expected): void
    {
        $this->assertSame($expected, $this->type->convertToDatabaseValue($value, $this->platform));
    }

    #[TestDox('convertToDatabaseValue() throws exception for invalid value')]
    #[TestWith([
        'x://::abc/?',
        'Could not convert PHP type "string" to "string". '
            . 'An error was triggered by the serialization: Unable to parse value as a URI.',
    ])]
    #[TestWith([
        1234,
        'Could not convert PHP type "int" to "string". An error was triggered by the serialization: '
            . 'Value must be a string or instance of Psr\Http\Message\UriInterface',
    ])]
    public function testConvertToDatabaseValueThrowsException(mixed $value, string $expectedExceptionMessage): void
    {
        $this->expectException(SerializationFailed::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $this->type->convertToDatabaseValue($value, $this->platform);
    }

    #[TestDox('convertToPHPValue() converts value to a UriInterface or null')]
    #[TestWith([new Uri('https://example.com/foo'), new Uri('https://example.com/foo')])]
    #[TestWith([null, null])]
    #[TestWith(['', null])]
    #[TestWith(['https://my.example.com/bar', new Uri('https://my.example.com/bar')])]
    public function testConvertToPHPValue(mixed $value, mixed $expected): void
    {
        if ($expected instanceof UriInterface) {
            // Use equality check because we can't check identity in this test.
            $this->assertEquals($expected, $this->type->convertToPHPValue($value, $this->platform));
        } else {
            $this->assertSame($expected, $this->type->convertToPHPValue($value, $this->platform));
        }
    }

    #[TestDox('convertToPHPValue() throws exception for invalid value')]
    public function testConvertToPHPValueThrowsException(): void
    {
        $this->expectException(ValueNotConvertible::class);
        $this->expectExceptionMessage('Could not convert database value "x://::abc/?" to Doctrine Type "url".');

        $this->type->convertToPHPValue('x://::abc/?', $this->platform);
    }
}
