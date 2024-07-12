<?php

declare(strict_types=1);

namespace App\Tests\Service\Analytics;

use App\Entity\AnalyticsDevice;
use App\Entity\AnalyticsEvent;
use App\Service\Analytics\AnalyticsDetails;
use App\Service\Analytics\AnalyticsDetailsFactory;
use App\Service\Analytics\AppDatabase;
use App\Service\AnalyticsEventService;
use Closure;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Laminas\Diactoros\UriFactory;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[TestDox('AppDatabase')]
class AppDatabaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private AnalyticsDetails $analyticsDetails;

    protected function setUp(): void
    {
        $this->analyticsDetails = new AnalyticsDetails(
            eventName: 'anEvent',
            url: (new UriFactory())->createUri('https://example.com'),
        );
    }

    #[TestDox('records an event using the web context')]
    public function testRecordEventFromWebContext(): void
    {
        $device = new AnalyticsDevice();
        $event = (new AnalyticsEvent())->setDevice($device);

        $analyticsEventService = Mockery::mock(AnalyticsEventService::class);
        $analyticsEventService
            ->expects('createAnalyticsEventFromDetails')
            ->with($this->analyticsDetails)
            ->andReturn($event);

        $entityManager = Mockery::mock(EntityManagerInterface::class);
        $entityManager->expects('wrapInTransaction')->with(Mockery::capture($transaction));
        $entityManager->expects('persist')->with($device);
        $entityManager->expects('persist')->with($event);
        $entityManager->expects('flush');

        $request = Request::create(uri: 'https://example.com/path/to/content', server: ['REMOTE_ADDR' => '127.0.0.1']);
        $response = new Response();

        $logger = new Logger('test');
        $factory = Mockery::mock(AnalyticsDetailsFactory::class);
        $factory
            ->expects('createFromWebContext')
            ->with('anEvent', $request, $response, ['abc' => 123])
            ->andReturn($this->analyticsDetails);

        $databaseService = new AppDatabase($analyticsEventService, $entityManager, $factory, $logger);
        $databaseService->recordEventFromWebContext('anEvent', $request, $response, ['abc' => 123]);

        // Call the transaction closure to assert its work.
        $this->assertInstanceOf(Closure::class, $transaction);
        $transaction($entityManager);
    }

    #[TestDox('records an event using analytics details')]
    public function testRecordEventFromDetails(): void
    {
        $device = new AnalyticsDevice();
        $event = (new AnalyticsEvent())->setDevice($device);

        $analyticsEventService = Mockery::mock(AnalyticsEventService::class);
        $analyticsEventService
            ->expects('createAnalyticsEventFromDetails')
            ->with($this->analyticsDetails)
            ->andReturn($event);

        $entityManager = Mockery::mock(EntityManagerInterface::class);
        $entityManager->expects('wrapInTransaction')->with(Mockery::capture($transaction));
        $entityManager->expects('persist')->with($device);
        $entityManager->expects('persist')->with($event);
        $entityManager->expects('flush');

        $logger = new Logger('test');
        $factory = Mockery::mock(AnalyticsDetailsFactory::class);

        $databaseService = new AppDatabase($analyticsEventService, $entityManager, $factory, $logger);
        $databaseService->recordEventFromDetails($this->analyticsDetails);

        // Call the transaction closure to assert its work.
        $this->assertInstanceOf(Closure::class, $transaction);
        $transaction($entityManager);
    }

    #[TestDox('logs an error if unable to write to the database')]
    public function testRecordEventFromDetailsWhenUnableToWriteToDatabase(): void
    {
        $analyticsEventService = Mockery::mock(AnalyticsEventService::class);

        $entityManager = Mockery::mock(EntityManagerInterface::class);
        $entityManager
            ->expects('wrapInTransaction')
            ->andThrow(new class extends RuntimeException implements ORMException {
            });

        $testHandler = new TestHandler();
        $logger = new Logger('test', [$testHandler]);
        $factory = Mockery::mock(AnalyticsDetailsFactory::class);

        $databaseService = new AppDatabase($analyticsEventService, $entityManager, $factory, $logger);
        $databaseService->recordEventFromDetails($this->analyticsDetails);

        $this->assertTrue($testHandler->hasErrorThatContains('Unable to write analytics to the database:'));
    }
}
