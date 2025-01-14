<?php

/**
 * This file is part of ramsey/website
 *
 * Copyright (c) Ben Ramsey <ben@ramsey.dev>
 *
 * ramsey/website is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Affero General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.
 *
 * ramsey/website is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with ramsey/website. If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace App\Entity;

use App\Doctrine\Traits\Timestampable;
use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidV7Generator;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

use function array_unique;

/**
 * Represents an authenticated user for the website
 *
 * @phpstan-type UserRoles list<'ROLE_USER' | 'ROLE_ADMIN' | 'ROLE_SUPER_ADMIN'>
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(fields: ['email'])]
#[ORM\HasLifecycleCallbacks]
class User implements PasswordAuthenticatedUserInterface, UserInterface
{
    use Timestampable;

    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidV7Generator::class)]
    private UuidInterface $id;

    #[ORM\Column(length: 100)]
    private string $name;

    /**
     * @var non-empty-string
     */
    #[ORM\Column(length: 180)]
    private string $email;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column(options: ['jsonb' => true])]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column(length: 255)]
    private string $password;

    /**
     * Returns the user's database identifier
     */
    public function getId(): UuidInterface
    {
        return $this->id;
    }

    /**
     * Returns the user's name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets the user's name
     */
    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Returns the user's email address
     *
     * @return non-empty-string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Sets the user's email address
     *
     * @param non-empty-string $email
     */
    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Returns the unique identifier for the user (i.e., email address)
     *
     * @return non-empty-string
     */
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /**
     * Returns the roles granted to the user
     *
     * @return UserRoles
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        /** @var UserRoles */
        return array_unique($roles);
    }

    /**
     * Sets the roles to grant to the user
     *
     * @param UserRoles $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Returns the user's hashed password
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Sets the hashed password for the user
     *
     * **IMPORTANT:** The password must be hashed before setting it on the user.
     */
    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }
}
