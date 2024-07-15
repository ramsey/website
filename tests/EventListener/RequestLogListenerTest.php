<?php

declare(strict_types=1);

namespace App\Tests\EventListener;

use App\Entity\AnalyticsDevice;
use App\EventListener\RequestLogListener;
use App\Service\Analytics\AnalyticsDetails;
use App\Service\Analytics\AnalyticsDetailsFactory;
use App\Service\AnalyticsDeviceService;
use DateTimeImmutable;
use Faker\Factory;
use Laminas\Diactoros\UriFactory;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Monolog\Handler\TestHandler;
use Monolog\LogRecord;
use Monolog\Logger;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

use function bin2hex;
use function json_encode;
use function md5;

#[TestDox('RequestLogListener')]
class RequestLogListenerTest extends TestCase
{
    use ClockSensitiveTrait;
    use MockeryPHPUnitIntegration;

    #[TestDox('logs request and response info for each request')]
    public function testLogsRequestResponseData(): void
    {
        $faker = Factory::create();
        $ip = $faker->ipv4();
        $userAgent = $faker->userAgent();

        $analyticsDetails = new AnalyticsDetails(
            eventName: 'anEvent',
            url: (new UriFactory())->createUri($faker->url()),
            geoCity: $faker->city(),
            geoCountryCode: $faker->countryCode(),
            geoLatitude: $faker->latitude(),
            geoLongitude: $faker->longitude(),
            geoSubdivisionCode: $faker->stateAbbr(), // @phpstan-ignore method.notFound
            ipAddress: $ip,
            ipAddressUserAgentHash: md5($ip . $userAgent, true),
            redirectUrl: (new UriFactory())->createUri($faker->url()),
            referrer: (new UriFactory())->createUri($faker->url()),
            serverEnvironment: ['foo' => 'bar'],
            userAgent: $userAgent,
        );

        $requestTime = new DateTimeImmutable('@1720843500.863559');
        $clock = static::mockTime(new DateTimeImmutable('@1720843501.386580'));

        $testHandler = new TestHandler();
        $appHealthLogger = new Logger('test_health', [$testHandler]);
        $appRequestLogger = new Logger('test_request', [$testHandler]);

        $request = Request::create(
            uri: 'https://foo.example.net/path/to/content',
            method: 'PUT',
            server: [
                'REQUEST_TIME_FLOAT' => $requestTime->format('U.u'),
            ],
            content: (string) json_encode(['foo' => 'bar']),
        );

        $response = new Response(
            content: 'Body of response.',
            status: 201,
        );

        $factory = Mockery::mock(AnalyticsDetailsFactory::class);
        $factory
            ->expects('createFromWebContext')
            ->with('request_complete', $request, $response)
            ->andReturn($analyticsDetails);

        $device = new AnalyticsDevice();
        $device->setCategory('Web Browser');
        $device->setDevice('desktop');
        $device->setFamily('Test Family');
        $device->setName('A Test Browser');
        $device->setOsFamily('An Operating System');

        $deviceService = Mockery::mock(AnalyticsDeviceService::class);
        $deviceService
            ->expects('getDevice')
            ->with($analyticsDetails->serverEnvironment)
            ->andReturn($device);

        $kernel = Mockery::mock(HttpKernelInterface::class);

        $listener = new RequestLogListener($factory, $appHealthLogger, $appRequestLogger, $deviceService, $clock);
        $event = new TerminateEvent($kernel, $request, $response);
        $listener($event);

        $this->assertTrue($testHandler->hasInfoThatPasses(function (LogRecord $record) use ($analyticsDetails): bool {
            $this->assertSame(
                'Responded 201 for PUT ' . $analyticsDetails->url,
                $record->message,
            );
            $this->assertSame(
                [
                    'exec_time' => '0.523021',
                    'device' => [
                        'category' => 'web browser',
                        'family' => 'Test Family',
                        'name' => 'A Test Browser',
                        'os' => 'An Operating System',
                        'type' => 'desktop',
                    ],
                    'geo' => [
                        'city' => $analyticsDetails->geoCity,
                        'country_code' => $analyticsDetails->geoCountryCode,
                        'latitude' => $analyticsDetails->geoLatitude,
                        'longitude' => $analyticsDetails->geoLongitude,
                        'subdivision_code' => $analyticsDetails->geoSubdivisionCode,
                    ],
                    'host' => $analyticsDetails->url->getHost(),
                    'ip' => $analyticsDetails->ipAddress,
                    'redirect_url' => $analyticsDetails->redirectUrl?->__toString(),
                    'referrer' => $analyticsDetails->referrer?->__toString(),
                    'request_method' => 'PUT',
                    'status_code' => 201,
                    'url' => $analyticsDetails->url->__toString(),
                    'user_agent' => $analyticsDetails->userAgent,
                    'visitor_hash' => bin2hex($analyticsDetails->ipAddressUserAgentHash),
                ],
                $record->context,
            );

            return true;
        }));
    }
}
