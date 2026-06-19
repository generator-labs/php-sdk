<?php declare(strict_types=1);

namespace GeneratorLabs\Tests;

use PHPUnit\Framework\TestCase;
use GeneratorLabs\Client;
use GeneratorLabs\Exception;

final class ClientTest extends TestCase
{
    private string $validAccountSid = 'AC12345678901234567890123456789012';
    private string $validAuthToken = '0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef';

    public function testClientInstantiationWithValidCredentials(): void
    {
        $client = new Client($this->validAccountSid, $this->validAuthToken);

        $this->assertInstanceOf(Client::class, $client);
        $this->assertEquals($this->validAccountSid, $client->account_sid());
        $this->assertEquals($this->validAuthToken, $client->api_token());
    }

    public function testClientThrowsExceptionWithInvalidAccountSid(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('invalid API account sid provided');

        new Client('invalid', $this->validAuthToken);
    }

    public function testClientThrowsExceptionWithInvalidAuthToken(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('invalid API access token provided');

        new Client($this->validAccountSid, 'invalid');
    }

    public function testDefaultUrlIsV4(): void
    {
        $client = new Client($this->validAccountSid, $this->validAuthToken);

        $this->assertEquals('https://api.generatorlabs.com/4.0/', $client->url());
    }

    public function testCanSetCustomUrl(): void
    {
        $client = new Client($this->validAccountSid, $this->validAuthToken);
        $customUrl = 'https://custom.api.example.com/';

        $client->url($customUrl);

        $this->assertEquals($customUrl, $client->url());
    }

    public function testRblResourceContainerIsAccessible(): void
    {
        $client = new Client($this->validAccountSid, $this->validAuthToken);

        $this->assertInstanceOf(\GeneratorLabs\API\RBL::class, $client->rbl);
    }

    public function testContactResourceContainerIsAccessible(): void
    {
        $client = new Client($this->validAccountSid, $this->validAuthToken);

        $this->assertInstanceOf(\GeneratorLabs\API\Contact::class, $client->contact);
    }

    public function testCertResourceContainerIsAccessible(): void
    {
        $client = new Client($this->validAccountSid, $this->validAuthToken);

        $this->assertInstanceOf(\GeneratorLabs\API\Cert::class, $client->cert);
    }

    public function testCertErrorsEndpointIsAccessible(): void
    {
        $client = new Client($this->validAccountSid, $this->validAuthToken);

        $this->assertInstanceOf(\GeneratorLabs\API\Cert\Errors::class, $client->cert->errors);
    }

    public function testCertMonitorsEndpointIsAccessible(): void
    {
        $client = new Client($this->validAccountSid, $this->validAuthToken);

        $this->assertInstanceOf(\GeneratorLabs\API\Cert\Monitors::class, $client->cert->monitors);
    }

    public function testCertProfilesEndpointIsAccessible(): void
    {
        $client = new Client($this->validAccountSid, $this->validAuthToken);

        $this->assertInstanceOf(\GeneratorLabs\API\Cert\Profiles::class, $client->cert->profiles);
    }

    public function testInvalidResourceThrowsException(): void
    {
        $client = new Client($this->validAccountSid, $this->validAuthToken);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('invalid resource invalid_resource');

        $client->invalid_resource;
    }

    public function testVersionConstant(): void
    {
        $this->assertEquals('2.0.1', Client::VERSION);
    }
}
