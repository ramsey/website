<?php

declare(strict_types=1);

namespace App\Tests\Controller\Admin;

use App\Controller\Admin\ShortUrlCrudController;
use App\Entity\ShortUrl;
use App\Entity\User;
use App\Repository\UserRepository;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use function assert;
use function urlencode;

#[TestDox('ShortUrlCrudController')]
class ShortUrlCrudControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = $this->createClient();
    }

    #[TestDox('::getEntityFqcn() returns FQCN of ShortUrl entity')]
    public function testGetEntityFqcn(): void
    {
        $this->assertSame(ShortUrl::class, ShortUrlCrudController::getEntityFqcn());
    }

    #[TestDox('redirects index to /login for a logged out user')]
    public function testRedirectIndexToLogin(): void
    {
        $this->client->request(
            'GET',
            '/admin?crudAction=index&crudControllerFqcn=' . urlencode(ShortUrlCrudController::class),
        );

        $this->assertResponseRedirects('/login');
    }

    #[Group('db')]
    #[TestDox('loads index for a logged-in admin user')]
    public function testIndexLoadsWhenLoggedIn(): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'admin-user@example.com']);
        assert($user instanceof User);

        $this->client->loginUser($user);
        $this->client->request(
            'GET',
            '/admin?crudAction=index&crudControllerFqcn=' . urlencode(ShortUrlCrudController::class),
        );

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Short URLs');
    }
}
