<?php

declare(strict_types=1);

namespace App\Tests\Controller\Admin;

use App\Controller\Admin\ShortUrlCrudController;
use App\Entity\ShortUrl;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Entity\ShortUrlService;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use function assert;
use function urlencode;

#[TestDox('ShortUrlCrudController')]
class ShortUrlCrudControllerTest extends WebTestCase
{
    use MockeryPHPUnitIntegration;

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

    public function testCreateEntity(): void
    {
        $manager = Mockery::mock(ShortUrlService::class);
        $manager->expects('generateSlug')->andReturn('generated-slug');

        $controller = new ShortUrlCrudController($manager);

        $shortUrl = $controller->createEntity(ShortUrl::class);

        $this->assertInstanceOf(ShortUrl::class, $shortUrl);
        $this->assertSame('generated-slug', $shortUrl->getSlug());
        $this->assertInstanceOf(DateTimeInterface::class, $shortUrl->getCreatedAt());
    }

    public function testPersistEntityWithoutCustomSlug(): void
    {
        $shortUrl = new ShortUrl();

        $manager = Mockery::mock(ShortUrlService::class);
        $manager->expects('checkAndSetCustomSlug')->never();

        $em = Mockery::mock(EntityManagerInterface::class);
        $em->expects('persist')->with($shortUrl);
        $em->expects('flush');

        $controller = new ShortUrlCrudController($manager);
        $controller->persistEntity($em, $shortUrl);

        $this->assertNull($shortUrl->getUpdatedAt());
    }

    public function testPersistEntityWithCustomSlug(): void
    {
        $shortUrl = (new ShortUrl())->setCustomSlug('custom-slug');

        $manager = Mockery::mock(ShortUrlService::class);
        $manager->expects('checkAndSetCustomSlug')->with($shortUrl, 'custom-slug')->andReturn($shortUrl);

        $em = Mockery::mock(EntityManagerInterface::class);
        $em->expects('persist')->with($shortUrl);
        $em->expects('flush');

        $controller = new ShortUrlCrudController($manager);
        $controller->persistEntity($em, $shortUrl);

        $this->assertNull($shortUrl->getUpdatedAt());
    }

    public function testUpdateEntityWithoutCustomSlug(): void
    {
        $shortUrl = new ShortUrl();

        $manager = Mockery::mock(ShortUrlService::class);
        $manager->expects('checkAndSetCustomSlug')->never();

        $em = Mockery::mock(EntityManagerInterface::class);
        $em->expects('persist')->with($shortUrl);
        $em->expects('flush');

        $controller = new ShortUrlCrudController($manager);
        $controller->updateEntity($em, $shortUrl);

        $this->assertInstanceOf(DateTimeInterface::class, $shortUrl->getUpdatedAt());
    }

    public function testUpdateEntityWithCustomSlug(): void
    {
        $shortUrl = (new ShortUrl())->setCustomSlug('custom-slug');

        $manager = Mockery::mock(ShortUrlService::class);
        $manager->expects('checkAndSetCustomSlug')->with($shortUrl, 'custom-slug')->andReturn($shortUrl);

        $em = Mockery::mock(EntityManagerInterface::class);
        $em->expects('persist')->with($shortUrl);
        $em->expects('flush');

        $controller = new ShortUrlCrudController($manager);
        $controller->updateEntity($em, $shortUrl);

        $this->assertInstanceOf(DateTimeInterface::class, $shortUrl->getUpdatedAt());
    }
}
