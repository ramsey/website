<?php

declare(strict_types=1);

namespace App\Tests\DataFixtures;

use App\Entity\User as UserEntity;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserFixtures extends Fixture
{
    public const string SUPER_ADMIN_USER = 'super-admin-user';
    public const string USER = 'user';

    private Generator $faker;

    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
        $this->faker = Factory::create();
    }

    public function load(ObjectManager $manager): void
    {
        $superAdminUser = new UserEntity();
        $superAdminUser->setName($this->faker->name());
        $superAdminUser->setEmail('super-admin-user@example.com');
        $superAdminUser->setPassword($this->passwordHasher->hashPassword($superAdminUser, $this->faker->password()));
        $superAdminUser->setRoles(['ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN']);
        $superAdminUser->setCreatedAt(new DateTimeImmutable('-9 weeks'));
        $superAdminUser->setUpdatedAt(new DateTimeImmutable('-8 weeks'));
        $manager->persist($superAdminUser);

        $adminUser = new UserEntity();
        $adminUser->setName($this->faker->name());
        $adminUser->setEmail('admin-user@example.com');
        $adminUser->setPassword($this->passwordHasher->hashPassword($adminUser, 'p4$$w0Rd'));
        $adminUser->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
        $adminUser->setCreatedAt(new DateTimeImmutable('-7 weeks'));
        $adminUser->setUpdatedAt(new DateTimeImmutable('-6 weeks'));
        $manager->persist($adminUser);

        $deletedUser = new UserEntity();
        $deletedUser->setName($this->faker->name());
        $deletedUser->setEmail($this->faker->safeEmail());
        $deletedUser->setPassword($this->passwordHasher->hashPassword($deletedUser, $this->faker->password()));
        $deletedUser->setRoles(['ROLE_USER']);
        $deletedUser->setCreatedAt(new DateTimeImmutable('-5 weeks'));
        $deletedUser->setUpdatedAt(new DateTimeImmutable('-4 weeks'));
        $deletedUser->setDeletedAt(new DateTimeImmutable('-3 weeks'));
        $manager->persist($deletedUser);

        $user = new UserEntity();
        $user->setName($this->faker->name());
        $user->setEmail($this->faker->safeEmail());
        $user->setPassword($this->passwordHasher->hashPassword($user, $this->faker->password()));
        $user->setRoles(['ROLE_USER']);
        $user->setCreatedAt(new DateTimeImmutable('-2 weeks'));
        $user->setUpdatedAt(new DateTimeImmutable('-1 weeks'));
        $manager->persist($user);

        $manager->flush();

        $this->addReference(self::SUPER_ADMIN_USER, $superAdminUser);
        $this->addReference(self::USER, $user);
    }
}
