<?php

declare(strict_types=1);

namespace App\Tests\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use function assert;

#[TestDox('DashboardController')]
class DashboardControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = $this->createClient();
    }

    #[TestDox('redirects to /login for a logged out user')]
    public function testRedirectToLogin(): void
    {
        $this->client->request('GET', '/admin');

        $this->assertResponseRedirects('/login');
    }

    #[Group('db')]
    #[TestDox('loads for a logged-in admin user')]
    public function testDashboardLoadsWhenLoggedIn(): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'admin-user@example.com']);
        assert($user instanceof User);

        $this->client->loginUser($user);
        $this->client->request('GET', '/admin');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('#main > h1', 'Hello');
    }
}
