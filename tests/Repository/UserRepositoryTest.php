<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Bundle\DoctrineBundle\Registry;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

use function sprintf;

#[Group('db')]
#[TestDox('UserRepository')]
class UserRepositoryTest extends KernelTestCase
{
    private UserRepository $repository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        /** @var Registry $doctrine */
        $doctrine = $kernel->getContainer()->get('doctrine');
        $entityManager = $doctrine->getManager();

        $this->repository = $entityManager->getRepository(User::class);
    }

    #[TestDox('upgrades user password and update time when calling upgradePassword()')]
    public function testUpgradePassword(): void
    {
        /** @var User $user */
        $user = $this->repository->createQueryBuilder('u')
            ->andWhere('u.deletedAt IS NULL')
            ->andWhere('JSONB_CONTAINS(u.roles, :role) = true')
            ->setParameter('role', '"ROLE_SUPER_ADMIN"')
            ->getQuery()
            ->getOneOrNullResult();

        $this->assertNotNull($user);

        $oldUpdatedAt = $user->getUpdatedAt();
        $oldHashedPassword = $user->getPassword();
        $newHashedPassword = 'h4$h3d_p4$$w0rd';

        $this->repository->upgradePassword($user, $newHashedPassword);

        $this->assertNotSame($oldHashedPassword, $user->getPassword());
        $this->assertSame($newHashedPassword, $user->getPassword());
        $this->assertNotSame($oldUpdatedAt?->format('U'), $user->getUpdatedAt()?->format('U'));
    }

    #[TestDox('throws exception when not passing App\\Entity\\User to upgradePassword()')]
    public function testUpgradePasswordThrowsException(): void
    {
        $user = new class implements PasswordAuthenticatedUserInterface {
            public function getPassword(): ?string
            {
                return null;
            }
        };

        $this->expectException(UnsupportedUserException::class);
        $this->expectExceptionMessage(sprintf(
            'Instances of "%s" are not supported.',
            $user::class,
        ));

        $this->repository->upgradePassword($user, 'new password');
    }
}
