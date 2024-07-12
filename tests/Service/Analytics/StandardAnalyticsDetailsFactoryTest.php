<?php

declare(strict_types=1);

namespace App\Tests\Service\Analytics;

use App\Service\Analytics\StandardAnalyticsDetailsFactory;
use Faker\Factory;
use Faker\Generator;
use GeoIp2\Exception\AddressNotFoundException;
use GeoIp2\Model\City;
use GeoIp2\ProviderInterface;
use Laminas\Diactoros\UriFactory;
use MaxMind\Db\Reader\InvalidDatabaseException;
use Mockery;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function hash_hmac;

#[TestDox('StandardAnalyticsDetailsFactory')]
class StandardAnalyticsDetailsFactoryTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        $this->faker = Factory::create();
    }

    #[TestDox('creates analytics details from the web context with defaults')]
    public function testCreateFromWebContextWithDefaults(): void
    {
        $secretKey = 'aSecretKey';
        $ipAddress = $this->faker->ipv4();
        $userAgent = $this->faker->userAgent();
        $expectedHash = hash_hmac('ripemd160', $ipAddress . $userAgent, $secretKey, true);

        $reader = Mockery::mock(ProviderInterface::class);
        $reader->expects('city')->with($ipAddress)->andThrow(new AddressNotFoundException());

        $uriFactory = new UriFactory();
        $testHandler = new TestHandler();
        $logger = new Logger('test', [$testHandler]);

        $request = Request::create(
            uri: 'https://example.com/path/to/content',
            server: [
                'HTTP_USER_AGENT' => $userAgent,
                'REMOTE_ADDR' => $ipAddress,
            ],
        );

        $response = new Response();

        $factory = new StandardAnalyticsDetailsFactory($secretKey, $reader, $uriFactory, $logger);
        $details = $factory->createFromWebContext('anEvent', $request, $response, ['a' => 1, 'b' => 2]);

        $this->assertSame('anEvent', $details->eventName);
        $this->assertNull($details->geoCity);
        $this->assertNull($details->geoCountryCode);
        $this->assertNull($details->geoLatitude);
        $this->assertNull($details->geoLongitude);
        $this->assertNull($details->geoSubdivisionCode);
        $this->assertSame($ipAddress, $details->ipAddress);
        $this->assertSame($expectedHash, $details->ipAddressUserAgentHash);
        $this->assertSame('en', $details->locale);
        $this->assertNull($details->redirectUrl);
        $this->assertNull($details->referrer);
        $this->assertArrayHasKey('REMOTE_ADDR', $details->serverEnvironment);
        $this->assertSame($ipAddress, $details->serverEnvironment['REMOTE_ADDR']);
        $this->assertArrayHasKey('HTTP_USER_AGENT', $details->serverEnvironment);
        $this->assertSame($userAgent, $details->serverEnvironment['HTTP_USER_AGENT']);
        $this->assertSame(
            [
                'http_method' => 'GET',
                'status_code' => 200,
                'a' => 1,
                'b' => 2,
            ],
            $details->tags,
        );
        $this->assertSame('https://example.com/path/to/content', $details->url->__toString());
        $this->assertSame($userAgent, $details->userAgent);
    }

    #[TestDox('escapes the :// in Archive.org redirect URLs to avoid problems in the analytics service')]
    #[TestWith(['https://archive/web/http%3A%2F%2Fexample.com/foo', 'https://archive/web/http://example.com/foo'])]
    #[TestWith(['https://archive/web/https%3A%2F%2Fexample.com/foo', 'https://archive/web/https://example.com/foo'])]
    #[TestWith(['http://archive/web/http%3A%2F%2Fexample.com/foo', 'http://archive/web/http://example.com/foo'])]
    #[TestWith(['http://archive/web/https%3A%2F%2Fexample.com/foo', 'http://archive/web/https://example.com/foo'])]
    public function testCreateFromWebContextWithRedirectUri(
        string $expectedRedirectUri,
        string $redirectUri,
    ): void {
        $referrer = $this->faker->url();

        $secretKey = 'aSecretKey';
        $ipAddress = $this->faker->ipv4();

        $reader = Mockery::mock(ProviderInterface::class);
        $reader->expects('city')->with($ipAddress)->andThrow(new AddressNotFoundException());

        $uriFactory = new UriFactory();
        $testHandler = new TestHandler();
        $logger = new Logger('test', [$testHandler]);

        $request = Request::create(
            uri: 'https://example.com/path/to/content',
            server: [
                'HTTP_REFERER' => $referrer,
                'REMOTE_ADDR' => $ipAddress,
            ],
        );

        $response = new Response(status: 302, headers: ['location' => $redirectUri]);

        $factory = new StandardAnalyticsDetailsFactory($secretKey, $reader, $uriFactory, $logger);
        $details = $factory->createFromWebContext('anEvent', $request, $response, []);

        $this->assertSame($expectedRedirectUri, $details->redirectUrl?->__toString());
        $this->assertSame($referrer, $details->referrer?->__toString());
    }

    #[TestDox('creates analytics details with HTTP method and status in tags')]
    public function testCreateFromWebContextWithAlternateHttpMethodAndStatus(): void
    {
        $secretKey = 'aSecretKey';
        $ipAddress = $this->faker->ipv4();

        $reader = Mockery::mock(ProviderInterface::class);
        $reader->expects('city')->with($ipAddress)->andThrow(new AddressNotFoundException());

        $uriFactory = new UriFactory();
        $testHandler = new TestHandler();
        $logger = new Logger('test', [$testHandler]);

        $request = Request::create(
            uri: 'https://example.com/path/to/content',
            method: 'POST',
            server: [
                'REMOTE_ADDR' => $ipAddress,
            ],
        );
        $response = new Response(status: 201);

        $factory = new StandardAnalyticsDetailsFactory($secretKey, $reader, $uriFactory, $logger);
        $details = $factory->createFromWebContext('anEvent', $request, $response, []);

        $this->assertSame(['http_method' => 'POST', 'status_code' => 201], $details->tags);
    }

    #[TestDox('creates analytics details with geo data')]
    public function testCreateFromWebContextWithGeoData(): void
    {
        $secretKey = 'aSecretKey';
        $ipAddress = $this->faker->ipv4();

        $city = new City([
            'city' => [
                'names' => ['en' => 'A City'],
            ],
            'country' => [
                'iso_code' => 'US',
            ],
            'location' => [
                'latitude' => 44.98,
                'longitude' => 93.2636,
            ],
            'subdivisions' => [
                [
                    'iso_code' => 'TN',
                ],
            ],
        ]);

        $reader = Mockery::mock(ProviderInterface::class);
        $reader->expects('city')->with($ipAddress)->andReturn($city);

        $uriFactory = new UriFactory();
        $testHandler = new TestHandler();
        $logger = new Logger('test', [$testHandler]);

        $request = Request::create(uri: 'https://example.com/path/to/content', server: ['REMOTE_ADDR' => $ipAddress]);
        $response = new Response();

        $factory = new StandardAnalyticsDetailsFactory($secretKey, $reader, $uriFactory, $logger);
        $details = $factory->createFromWebContext('anEvent', $request, $response, []);

        $this->assertSame('A City', $details->geoCity);
        $this->assertSame('US', $details->geoCountryCode);
        $this->assertSame(44.98, $details->geoLatitude);
        $this->assertSame(93.2636, $details->geoLongitude);
        $this->assertSame('TN', $details->geoSubdivisionCode);
    }

    #[TestDox('creates analytics details with Digital Ocean connecting IP address')]
    public function testCreateFromWebContextWithDoConnectingIp(): void
    {
        $secretKey = 'aSecretKey';
        $ipAddress = $this->faker->ipv4();
        $userAgent = $this->faker->userAgent();
        $expectedHash = hash_hmac('ripemd160', $ipAddress . $userAgent, $secretKey, true);

        $reader = Mockery::mock(ProviderInterface::class);
        $reader->expects('city')->with($ipAddress)->andThrow(new AddressNotFoundException());

        $uriFactory = new UriFactory();
        $testHandler = new TestHandler();
        $logger = new Logger('test', [$testHandler]);

        $request = Request::create(
            uri: 'https://example.com/',
            server: [
                'HTTP_DO_CONNECTING_IP' => $ipAddress,
                'HTTP_USER_AGENT' => $userAgent,
                'REMOTE_ADDR' => 'should not be used',
            ],
        );
        $response = new Response(status: 201);

        $factory = new StandardAnalyticsDetailsFactory($secretKey, $reader, $uriFactory, $logger);
        $details = $factory->createFromWebContext('anEvent', $request, $response, []);

        $this->assertSame($ipAddress, $details->ipAddress);
        $this->assertSame($expectedHash, $details->ipAddressUserAgentHash);
    }

    #[TestDox('creates analytics details and logs when unable to read geo IP database')]
    public function testCreateFromWebContextWhenUnableToReadGeoIpDb(): void
    {
        $secretKey = 'aSecretKey';
        $ipAddress = $this->faker->ipv4();

        $reader = Mockery::mock(ProviderInterface::class);
        $reader
            ->expects('city')
            ->with($ipAddress)
            ->andThrow(new InvalidDatabaseException('bad geo IP database'));

        $uriFactory = new UriFactory();
        $testHandler = new TestHandler();
        $logger = new Logger('test', [$testHandler]);

        $request = Request::create(uri: 'https://example.com/path/to/content', server: ['REMOTE_ADDR' => $ipAddress]);
        $response = new Response();

        $factory = new StandardAnalyticsDetailsFactory($secretKey, $reader, $uriFactory, $logger);
        $details = $factory->createFromWebContext('anotherEvent', $request, $response, []);

        $this->assertSame('anotherEvent', $details->eventName);
        $this->assertTrue($testHandler->hasErrorThatContains('Unable to read from geo IP database:'));
    }
}
