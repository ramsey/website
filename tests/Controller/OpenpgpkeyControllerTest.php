<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class OpenpgpkeyControllerTest extends WebTestCase
{
    #[TestDox('Request to /.well-known/openpgpkey/policy responds with 200')]
    #[TestWith(['benramsey.com'])]
    #[TestWith(['ramsey.dev'])]
    public function testPolicyResponseForDirectMethod(string $host): void
    {
        $client = static::createClient();
        $client->request('GET', "https://{$host}/.well-known/openpgpkey/policy");

        /** @var Response $response */
        $response = $client->getResponse();

        $this->assertResponseIsSuccessful();
        $this->assertSame(
            "# Policy flags for domain {$host}\n",
            (string) $response->getContent(),
        );
        $this->assertResponseHeaderSame('access-control-allow-origin', '*');
        $this->assertResponseHeaderSame('content-type', 'text/plain; charset=utf-8');
        $this->assertResponseHeaderSame(
            'cache-control',
            'max-age=604800, public, stale-while-revalidate=86400',
        );
    }

    #[TestDox('Request to openpgpkey.hostname/.well-known/openpgpkey/hostname/policy responds with 200')]
    #[TestWith(['benramsey.com'])]
    #[TestWith(['ramsey.dev'])]
    public function testPolicyResponseForAdvancedMethod(string $host): void
    {
        $client = static::createClient();
        $client->request('GET', "https://openpgpkey.{$host}/.well-known/openpgpkey/{$host}/policy");

        /** @var Response $response */
        $response = $client->getResponse();

        $this->assertResponseIsSuccessful();
        $this->assertSame(
            "# Policy flags for domain {$host}\n",
            (string) $response->getContent(),
        );
        $this->assertResponseHeaderSame('access-control-allow-origin', '*');
        $this->assertResponseHeaderSame('content-type', 'text/plain; charset=utf-8');
        $this->assertResponseHeaderSame(
            'cache-control',
            'max-age=604800, public, stale-while-revalidate=86400',
        );
    }

    #[TestDox('Request to /.well-known/openpgpkey/policy responds with 404 for bad host')]
    #[TestWith(['benramsey.dev'])]
    public function testPolicyResponseIsNotFoundForDirectMethod(string $host): void
    {
        $client = static::createClient();
        $client->request('GET', "https://{$host}/.well-known/openpgpkey/policy");

        $this->assertResponseStatusCodeSame(404);
    }

    #[TestDox('Request to openpgpkey.hostname/.well-known/openpgpkey/hostname/policy responds with 404 for bad host')]
    #[TestWith(['benramsey.dev'])]
    public function testPolicyResponseIsNotFoundForAdvancedMethod(string $host): void
    {
        $client = static::createClient();
        $client->request('GET', "https://openpgpkey.{$host}/.well-known/openpgpkey/{$host}/policy");

        $this->assertResponseStatusCodeSame(404);
    }

    #[TestDox('Request to /.well-known/openpgpkey/hu/id responds with 200')]
    #[TestWith(['benramsey.com', 'qpui546ptjbsz3rqaetbdz8wj9op6nur'])]
    #[TestWith(['ramsey.dev', 'qpui546ptjbsz3rqaetbdz8wj9op6nur'])]
    #[TestWith(['ramsey.dev', 't5s8ztdbon8yzntexy6oz5y48etqsnbb'])]
    public function testKeyResponseForDirectMethod(string $host, string $id): void
    {
        $client = static::createClient();
        $client->request('GET', "https://{$host}/.well-known/openpgpkey/hu/{$id}");

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('access-control-allow-origin', '*');
        $this->assertResponseHeaderSame('content-type', 'application/octet-stream');
        $this->assertResponseHeaderSame(
            'cache-control',
            'max-age=604800, public, stale-while-revalidate=86400',
        );
    }

    #[TestDox('Request to openpgpkey.hostname/.well-known/openpgpkey/hostname/hu/id responds with 200')]
    #[TestWith(['benramsey.com', 'qpui546ptjbsz3rqaetbdz8wj9op6nur'])]
    #[TestWith(['ramsey.dev', 'qpui546ptjbsz3rqaetbdz8wj9op6nur'])]
    #[TestWith(['ramsey.dev', 't5s8ztdbon8yzntexy6oz5y48etqsnbb'])]
    public function testKeyResponseForAdvancedMethod(string $host, string $id): void
    {
        $client = static::createClient();
        $client->request('GET', "https://openpgpkey.{$host}/.well-known/openpgpkey/{$host}/hu/{$id}");

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('access-control-allow-origin', '*');
        $this->assertResponseHeaderSame('content-type', 'application/octet-stream');
        $this->assertResponseHeaderSame(
            'cache-control',
            'max-age=604800, public, stale-while-revalidate=86400',
        );
    }

    #[TestDox('Request to /.well-known/openpgpkey/hu/id responds with 404')]
    #[TestWith(['benramsey.com', 'foobar'])]
    #[TestWith(['ramsey.dev', 'foobar'])]
    #[TestWith(['ben.ramsey.dev', 't5s8ztdbon8yzntexy6oz5y48etqsnbb'])]
    public function testKeyResponseIsNotFoundForDirectMethod(string $host, string $id): void
    {
        $client = static::createClient();
        $client->request('GET', "https://{$host}/.well-known/openpgpkey/hu/{$id}");

        $this->assertResponseStatusCodeSame(404);
    }

    #[TestDox('Request to openpgpkey.hostname/.well-known/openpgpkey/hostname/hu/id responds with 404')]
    #[TestWith(['benramsey.com', 'foobar'])]
    #[TestWith(['ramsey.dev', 'foobar'])]
    #[TestWith(['ben.ramsey.dev', 't5s8ztdbon8yzntexy6oz5y48etqsnbb'])]
    public function testKeyResponseIsNotFoundForAdvancedMethod(string $host, string $id): void
    {
        $client = static::createClient();
        $client->request('GET', "https://openpgpkey.{$host}/.well-known/openpgpkey/{$host}/hu/{$id}");

        $this->assertResponseStatusCodeSame(404);
    }
}
