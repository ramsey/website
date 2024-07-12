<?php

declare(strict_types=1);

namespace App\Tests\DataFixtures;

use App\Entity\AnalyticsDevice;
use App\Entity\AnalyticsEvent;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

use function hash_hmac;

final class AnalyticsEventFixtures extends Fixture
{
    private Generator $faker;

    public function __construct(
        #[Autowire('%app.service.analytics.secret_key%')] private readonly string $analyticsSecretKey,
    ) {
        $this->faker = Factory::create();
    }

    /**
     * @return list<class-string<FixtureInterface>>
     */
    public function getDependencies(): array
    {
        return [
            AnalyticsDeviceFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        foreach (AnalyticsDeviceFixtures::DEVICES as $name => $server) {
            $ipAddress = $this->faker->ipv4();
            $userAgent = $server['HTTP_USER_AGENT'];

            /** @var AnalyticsDevice $device */
            $device = $this->getReference($name);

            for ($i = 2; $i > 0; $i--) {
                $event = $this->createAnalyticsEvent($ipAddress, $userAgent, $device);
                $manager->persist($event);
            }
        }

        $manager->flush();
    }

    private function createAnalyticsEvent(string $ipAddress, string $userAgent, AnalyticsDevice $device): AnalyticsEvent
    {
        $hash = hash_hmac('ripemd160', $ipAddress . $userAgent, $this->analyticsSecretKey, true);

        return (new AnalyticsEvent())
            ->setDevice($device)
            ->setGeoCity($this->faker->city())
            ->setGeoCountryCode($this->faker->countryCode())
            ->setGeoLatitude($this->faker->latitude())
            ->setGeoLongitude($this->faker->longitude())
            ->setGeoSubdivisionCode($this->faker->stateAbbr()) // @phpstan-ignore method.notFound
            ->setHostname($this->faker->domainName())
            ->setIpUserAgentHash($hash)
            ->setLocale($this->faker->locale())
            ->setName('pageview')
            ->setTags(['foo' => 'bar', 'baz' => 'qux'])
            ->setUri($this->faker->url())
            ->setUserAgent($userAgent)
            ->setCreatedAt($this->faker->dateTimeBetween('-2 years', '-1 year'))
            ->setUpdatedAt($this->faker->dateTimeBetween('-1 year', 'now'));
    }
}
