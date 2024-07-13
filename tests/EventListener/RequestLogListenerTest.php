<?php

declare(strict_types=1);

namespace App\Tests\EventListener;

use App\EventListener\RequestLogListener;
use DateTimeImmutable;
use Faker\Factory;
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

use function json_encode;

#[TestDox('RequestLogListener')]
class RequestLogListenerTest extends TestCase
{
    use ClockSensitiveTrait;
    use MockeryPHPUnitIntegration;

    #[TestDox('logs request and response info for each request')]
    public function testLogsRequestResponseData(): void
    {
        $faker = Factory::create();
        $userAgent = $faker->userAgent();

        $requestTime = new DateTimeImmutable('@1720843500.863559');
        $clock = static::mockTime(new DateTimeImmutable('@1720843501.386580'));

        $testHandler = new TestHandler();
        $logger = new Logger('test', [$testHandler]);

        $request = Request::create(
            uri: 'https://foo.example.net/path/to/content',
            method: 'PUT',
            cookies: [
                'testCookie' => 'this cookie should not appear in the logs',
            ],
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_HOST' => 'foo.example.net',
                'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'HTTP_ACCEPT_CHARSET' => 'utf-8',
                'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
                'HTTP_ACCEPT_LANGUAGE' => 'es-MX',
                'HTTP_USER_AGENT' => $userAgent,
                'REQUEST_TIME_FLOAT' => $requestTime->format('U.u'),
            ],
            content: (string) json_encode(['foo' => 'bar']),
        );

        $response = new Response(
            content: 'Body of response.',
            status: 201,
            headers: [
                'cache-control' => 'no-cache, private',
                'content-type' => 'text/plain; charset=utf-8',
                'link' => '<https://example.com/path/to/alt/content>; rel=alternate',
                'date' => 'Sat, 13 Jul 2024 04:17:53 GMT',
            ],
        );

        $kernel = Mockery::mock(HttpKernelInterface::class);

        $listener = new RequestLogListener($logger, $clock);
        $event = new TerminateEvent($kernel, $request, $response);
        $listener($event);

        $this->assertTrue($testHandler->hasInfoThatPasses(function (LogRecord $record) use ($userAgent): bool {
            $this->assertSame('Responded 201 for PUT https://foo.example.net/path/to/content', $record->message);
            $this->assertSame(
                [
                    'url' => 'https://foo.example.net/path/to/content',
                    'request' => [
                        'method' => 'PUT',
                        'headers' => [
                            'host' => ['foo.example.net'],
                            'user-agent' => [$userAgent],
                            'accept' => ['text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'],
                            'accept-language' => ['es-MX'],
                            'accept-charset' => ['utf-8'],
                            'content-type' => ['application/json'],
                            'accept-encoding' => ['gzip, deflate, br'],
                        ],
                    ],
                    'response' => [
                        'code' => 201,
                        'headers' => [
                            'cache-control' => ['no-cache, private'],
                            'content-type' => ['text/plain; charset=utf-8'],
                            'link' => ['<https://example.com/path/to/alt/content>; rel=alternate'],
                            'date' => ['Sat, 13 Jul 2024 04:17:53 GMT'],
                        ],
                    ],
                    'exec_time' => '0.523021',
                ],
                $record->context,
            );

            return true;
        }));
    }
}
