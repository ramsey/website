<?php

declare(strict_types=1);

namespace App\Tests\DataFixtures;

use App\Service\AnalyticsDeviceService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class AnalyticsDeviceFixtures extends Fixture
{
    public const string DEVICE_ARCHIVE_ORG_BOT = 'Archive.org Bot';
    public const string DEVICE_CHATGPT_USER = 'ChatGPT User';
    public const string DEVICE_CHROMIUM = 'Chromium';
    public const string DEVICE_EDGE = 'Edge';
    public const string DEVICE_FIREFOX = 'Firefox';
    public const string DEVICE_GOOGLE_BOT = 'Google Bot';
    public const string DEVICE_IPHONE = 'iPhone';
    public const string DEVICE_NINTENDO_SWITCH = 'Nintendo Switch';
    public const string DEVICE_PIXEL = 'Pixel';
    public const string DEVICE_ROKU = 'Roku';
    public const string DEVICE_SAFARI = 'Safari';

    public const array DEVICES = [
        self::DEVICE_ARCHIVE_ORG_BOT => [
            'HTTP_USER_AGENT' => 'archive.org_bot',
        ],
        self::DEVICE_CHATGPT_USER => [
            'HTTP_USER_AGENT' => 'ChatGPT-User',
        ],
        self::DEVICE_CHROMIUM => [
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 '
                . '(KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
            'HTTP_SEC_CH_UA' => '"Not(A:Brand";v="24", "Chromium";v="122"',
            'HTTP_SEC_CH_UA_ARCH' => '"arm"',
            'HTTP_SEC_CH_UA_MOBILE' => '?0',
            'HTTP_SEC_CH_UA_MODEL' => '""',
            'HTTP_SEC_CH_UA_PLATFORM' => '"macOS"',
            'HTTP_SEC_CH_UA_PLATFORM_VERSION' => '"14.5.0"',
            'HTTP_SEC_CH_UA_FULL_VERSION_LIST' => '"Not(A:Brand";v="24.0.0.0", "Chromium";v="122.0.6261.111"',
            'HTTP_SEC_CH_UA_BITNESS' => '"64"',
            'HTTP_SEC_CH_UA_WOW64' => '?0',
        ],
        self::DEVICE_EDGE => [
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 '
                . '(KHTML, like Gecko) Chrome/68.0.2704.79 Safari/537.36 Edge/18.014',
        ],
        self::DEVICE_FIREFOX => [
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:15.0) Gecko/20100101 Firefox/15.0.1',
        ],
        self::DEVICE_GOOGLE_BOT => [
            'HTTP_USER_AGENT' => 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; Googlebot/2.1; '
                . '+http://www.google.com/bot.html) Chrome/W.X.Y.Z Safari/537.36',
        ],
        self::DEVICE_IPHONE => [
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (iPhone14,3; U; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/602.1.50 '
                . '(KHTML, like Gecko) Version/10.0 Mobile/19A346 Safari/602.1',
        ],
        self::DEVICE_NINTENDO_SWITCH => [
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Nintendo Switch; WifiWebAuthApplet) AppleWebKit/601.6 '
                . '(KHTML, like Gecko) NF/4.0.0.5.10 NintendoBrowser/5.1.0.13343',
        ],
        self::DEVICE_PIXEL => [
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Linux; Android 11; Pixel 5) AppleWebKit/537.36 '
                . '(KHTML, like Gecko) Chrome/90.0.4430.91 Mobile Safari/537.36',
            'HTTP_SEC_CH_UA' => '"Not?A_Brand";v="8", "Chromium";v="108", "Google Chrome";v="108"',
            'HTTP_SEC_CH_UA_ARCH' => '""',
            'HTTP_SEC_CH_UA_MOBILE' => '?1',
            'HTTP_SEC_CH_UA_MODEL' => '"Pixel 5"',
            'HTTP_SEC_CH_UA_PLATFORM' => '"Android"',
            'HTTP_SEC_CH_UA_PLATFORM_VERSION' => '"11"',
            'HTTP_SEC_CH_UA_FULL_VERSION_LIST' => '"Not?A_Brand";v="8.0.0.0", "Chromium";v="108.0.5359.124", '
                . '"Google Chrome";v="108.0.5359.124"',
            'HTTP_SEC_CH_UA_BITNESS' => '"64"',
            'HTTP_SEC_CH_UA_WOW64' => '?0',
        ],
        self::DEVICE_ROKU => [
            'HTTP_USER_AGENT' => 'Roku4640X/DVP-7.70 (297.70E04154A)',
        ],
        self::DEVICE_SAFARI => [
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 '
                . '(KHTML, like Gecko) Version/17.5 Safari/605.1.15',
        ],
    ];

    public function __construct(private readonly AnalyticsDeviceService $deviceService)
    {
    }

    public function load(ObjectManager $manager): void
    {
        foreach (self::DEVICES as $name => $server) {
            $device = $this->deviceService->getDevice($server);
            $manager->persist($device);
            $this->addReference($name, $device);
        }

        $manager->flush();
    }
}
