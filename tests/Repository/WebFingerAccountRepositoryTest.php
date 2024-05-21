<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\WebFingerAccount;
use App\Repository\WebFingerAccountRepository;
use LogicException;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Psr\Link\LinkInterface;

class WebFingerAccountRepositoryTest extends TestCase
{
    private WebFingerAccountRepository $repository;

    protected function setUp(): void
    {
        $this->repository = new WebFingerAccountRepository(
            __DIR__ . '/fixtures/webfinger_account.php',
        );
    }

    #[TestDox('getClassName() returns App\Entity\WebFingerAccount')]
    public function testGetClassName(): void
    {
        $this->assertEquals(WebFingerAccount::class, $this->repository->getClassName());
    }

    #[TestDox('find() throws exception')]
    public function testFindThrowsException(): void
    {
        $this->expectException(LogicException::class);

        $this->repository->find('foo');
    }

    #[TestDox('findAll() returns all WebFinger account entities')]
    public function testFindAll(): void
    {
        $accounts = $this->repository->findAll();

        $this->assertCount(3, $accounts);
        $this->assertContainsOnlyInstancesOf(WebFingerAccount::class, $accounts);
    }

    #[TestDox('findBy() with hostname returns expected values')]
    public function testFindByHostname(): void
    {
        $accounts1 = $this->repository->findBy(['hostname' => 'one.example.com']);
        $accounts2 = $this->repository->findBy(['hostname' => 'two.example.com']);

        $this->assertCount(2, $accounts1);
        $this->assertCount(1, $accounts2);
        $this->assertContainsOnlyInstancesOf(WebFingerAccount::class, $accounts1);
        $this->assertContainsOnlyInstancesOf(WebFingerAccount::class, $accounts2);

        $this->assertSame('one.example.com', $accounts1[0]->hostname);
        $this->assertSame('acct:frodo@one.example.com', $accounts1[0]->account);
        $this->assertSame('acct:frodo@one.example.com', $accounts1[0]->subject);
        $this->assertSame(['acct:fbaggins@one.example.com'], $accounts1[0]->aliases);
        $this->assertCount(2, $accounts1[0]->links);
        $this->assertContainsOnlyInstancesOf(LinkInterface::class, $accounts1[0]->links);
        $this->assertSame(
            [
                'https://schema.org/name' => 'Frodo Baggins',
                'https://schema.org/email' => 'frodo@one.example.com',
            ],
            $accounts1[0]->properties,
        );

        $this->assertSame(['me'], $accounts1[0]->links[0]->getRels());
        $this->assertSame('https://frodo.one.example.com', $accounts1[0]->links[0]->getHref());
        $this->assertSame(['type' => 'text/html'], $accounts1[0]->links[0]->getAttributes());

        $this->assertSame(['http://webfinger.net/rel/profile-page'], $accounts1[0]->links[1]->getRels());
        $this->assertSame('https://frodo.one.example.com', $accounts1[0]->links[1]->getHref());
        $this->assertSame(['type' => 'text/html'], $accounts1[0]->links[1]->getAttributes());

        $this->assertSame('one.example.com', $accounts1[1]->hostname);
        $this->assertSame('acct:samwise@one.example.com', $accounts1[1]->account);
        $this->assertSame('acct:samwise@one.example.com', $accounts1[1]->subject);
        $this->assertSame([], $accounts1[1]->aliases);
        $this->assertCount(1, $accounts1[1]->links);
        $this->assertContainsOnlyInstancesOf(LinkInterface::class, $accounts1[1]->links);
        $this->assertSame([], $accounts1[1]->properties);

        $this->assertSame(['me'], $accounts1[1]->links[0]->getRels());
        $this->assertSame('https://samwise.one.example.com', $accounts1[1]->links[0]->getHref());
        $this->assertSame(['type' => 'text/html'], $accounts1[1]->links[0]->getAttributes());

        $this->assertSame('two.example.com', $accounts2[0]->hostname);
        $this->assertSame('acct:pippin@two.example.com', $accounts2[0]->account);
        $this->assertSame('acct:pippin@two.example.com', $accounts2[0]->subject);
        $this->assertSame([], $accounts2[0]->aliases);
        $this->assertSame([], $accounts2[0]->links);
        $this->assertSame([], $accounts2[0]->properties);
    }

    #[TestDox('findBy() with account returns expected values')]
    public function testFindByAccount(): void
    {
        $accounts = $this->repository->findBy(['account' => 'acct:samwise@one.example.com']);

        $this->assertCount(1, $accounts);
        $this->assertContainsOnlyInstancesOf(WebFingerAccount::class, $accounts);

        $this->assertSame('one.example.com', $accounts[0]->hostname);
        $this->assertSame('acct:samwise@one.example.com', $accounts[0]->account);
        $this->assertSame('acct:samwise@one.example.com', $accounts[0]->subject);
        $this->assertSame([], $accounts[0]->aliases);
        $this->assertCount(1, $accounts[0]->links);
        $this->assertContainsOnlyInstancesOf(LinkInterface::class, $accounts[0]->links);
        $this->assertSame([], $accounts[0]->properties);

        $this->assertSame(['me'], $accounts[0]->links[0]->getRels());
        $this->assertSame('https://samwise.one.example.com', $accounts[0]->links[0]->getHref());
        $this->assertSame(['type' => 'text/html'], $accounts[0]->links[0]->getAttributes());
    }

    #[TestDox('findBy() with hostname and account returns expected values')]
    public function testFindByHostnameAndAccount(): void
    {
        $accounts = $this->repository->findBy([
            'hostname' => 'two.example.com',
            'account' => 'acct:pippin@two.example.com',
        ]);

        $this->assertCount(1, $accounts);
        $this->assertContainsOnlyInstancesOf(WebFingerAccount::class, $accounts);

        $this->assertSame('two.example.com', $accounts[0]->hostname);
        $this->assertSame('acct:pippin@two.example.com', $accounts[0]->account);
        $this->assertSame('acct:pippin@two.example.com', $accounts[0]->subject);
        $this->assertSame([], $accounts[0]->aliases);
        $this->assertSame([], $accounts[0]->links);
        $this->assertSame([], $accounts[0]->properties);
    }

    #[TestDox('findBy() returns empty array when no entities found')]
    public function testFindByReturnsEmptyArray(): void
    {
        $accounts = $this->repository->findBy([
            'hostname' => 'foo.example.com',
            'account' => 'acct:pippin@two.example.com',
        ]);

        $this->assertEmpty($accounts);
    }

    #[TestDox('findOneBy() returns only one entity')]
    public function testFindOneBy(): void
    {
        $account = $this->repository->findOneBy([
            'hostname' => 'one.example.com',
            'account' => 'acct:frodo@one.example.com',
        ]);

        $this->assertNotNull($account);
        $this->assertSame('one.example.com', $account->hostname);
        $this->assertSame('acct:frodo@one.example.com', $account->account);
        $this->assertSame('acct:frodo@one.example.com', $account->subject);
        $this->assertSame(['acct:fbaggins@one.example.com'], $account->aliases);
        $this->assertCount(2, $account->links);
        $this->assertContainsOnlyInstancesOf(LinkInterface::class, $account->links);
        $this->assertSame(
            [
                'https://schema.org/name' => 'Frodo Baggins',
                'https://schema.org/email' => 'frodo@one.example.com',
            ],
            $account->properties,
        );
    }

    #[TestDox('findOneBy() returns null when an entity is not found')]
    public function testFindOneByReturnsNullWhenNoEntityFound(): void
    {
        $account = $this->repository->findOneBy([
            'hostname' => 'one.example.com',
            'account' => 'acct:gandalf@one.example.com',
        ]);

        $this->assertNull($account);
    }

    #[TestDox('findOneBy() returns null when either criteria are not provided')]
    public function testFindOneByWithoutEitherCriteria(): void
    {
        // @phpstan-ignore argument.type
        $account1 = $this->repository->findOneBy(['hostname' => 'one.example.com']);

        // @phpstan-ignore argument.type
        $account2 = $this->repository->findOneBy(['account' => 'acct:samwise@one.example.com']);

        $this->assertNull($account1);
        $this->assertNull($account2);
    }
}
