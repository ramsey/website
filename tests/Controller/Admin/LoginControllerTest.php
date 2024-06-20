<?php

declare(strict_types=1);

namespace App\Tests\Controller\Admin;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[Group('db')]
#[TestDox('LoginController')]
class LoginControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    #[TestDox('does not allow login with invalid email address')]
    public function testLoginWithInvalidEmailAddress(): void
    {
        // Denied - Can't login with invalid email address.
        $this->client->request('GET', '/login');
        $this->assertResponseIsSuccessful();

        $this->client->submitForm('Sign in', [
            '_username' => 'doesNotExist@example.com',
            '_password' => 'password',
        ]);

        $this->assertResponseRedirects('/login');
        $this->client->followRedirect();

        // Ensure we do not reveal if the user exists or not.
        $this->assertSelectorTextContains('.alert-danger', 'Invalid credentials.');
    }

    #[TestDox('does not allow login with invalid password')]
    public function testLoginWithInvalidPassword(): void
    {
        // Denied - Can't login with invalid password.
        $this->client->request('GET', '/login');
        $this->assertResponseIsSuccessful();

        $this->client->submitForm('Sign in', [
            '_username' => 'admin-user@example.com',
            '_password' => 'bad-password',
        ]);

        $this->assertResponseRedirects('/login');
        $this->client->followRedirect();

        // Ensure we do not reveal the user exists but the password is wrong.
        $this->assertSelectorTextContains('.alert-danger', 'Invalid credentials.');
    }

    #[TestDox('allows login with valid credentials')]
    public function testLoginWithValidCredentials(): void
    {
        // Success - Login with valid credentials is allowed.
        $this->client->request('GET', '/login');
        $this->assertResponseIsSuccessful();

        $this->client->submitForm('Sign in', [
            '_username' => 'admin-user@example.com',
            '_password' => 'p4$$w0Rd',
        ]);

        $this->assertResponseRedirects('/');
        $this->client->followRedirect();

        $this->assertSelectorNotExists('.alert-danger');
        $this->assertResponseIsSuccessful();
    }

    #[TestDox('redirects to home on logout')]
    public function testLogout(): void
    {
        $this->client->request('GET', '/logout');

        $this->assertResponseRedirects('/');
        $this->client->followRedirect();

        $this->assertSelectorNotExists('.alert-danger');
        $this->assertResponseIsSuccessful();
    }
}
