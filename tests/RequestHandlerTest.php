<?php declare(strict_types=1);

namespace GeneratorLabs\Tests;

use PHPUnit\Framework\TestCase;
use GeneratorLabs\Client;

final class RequestHandlerTest extends TestCase
{
    private string $validAccountSid = 'AC12345678901234567890123456789012';
    private string $validAuthToken = '0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef';

    public function testArrayConvertedToCommaSeparatedInPost(): void
    {
        // Create a mock Guzzle handler that captures the request
        $capturedBody = '';

        $mock = new \GuzzleHttp\Handler\MockHandler([
            new \GuzzleHttp\Psr7\Response(200, [], json_encode([
                'status_code' => 200,
                'status_message' => 'OK',
                'data' => []
            ]))
        ]);

        $history = [];
        $historyMiddleware = \GuzzleHttp\Middleware::history($history);

        $handlerStack = \GuzzleHttp\HandlerStack::create($mock);
        $handlerStack->push($historyMiddleware);

        $client = new Client($this->validAccountSid, $this->validAuthToken);

        // Use reflection to replace the HTTP client on the hosts resource
        $rbl = $client->rbl;
        $hosts = $rbl->hosts;

        $reflection = new \ReflectionClass($hosts);
        $prop = $reflection->getProperty('http_client');
        $prop->setAccessible(true);
        $prop->setValue($hosts, new \GuzzleHttp\Client([
            'handler' => $handlerStack,
            'base_uri' => 'https://api.generatorlabs.com/4.0/',
        ]));

        $hosts->create([
            'name' => 'Test Host',
            'host' => '1.2.3.4',
            'contact_group' => [
                'CG11111111111111111111111111111111',
                'CG22222222222222222222222222222222'
            ]
        ]);

        $this->assertCount(1, $history);

        $body = (string) $history[0]['request']->getBody();
        parse_str($body, $params);

        $this->assertEquals(
            'CG11111111111111111111111111111111,CG22222222222222222222222222222222',
            $params['contact_group']
        );
        $this->assertEquals('Test Host', $params['name']);
    }

    public function testStringValueUnchanged(): void
    {
        $mock = new \GuzzleHttp\Handler\MockHandler([
            new \GuzzleHttp\Psr7\Response(200, [], json_encode([
                'status_code' => 200,
                'status_message' => 'OK',
                'data' => []
            ]))
        ]);

        $history = [];
        $historyMiddleware = \GuzzleHttp\Middleware::history($history);

        $handlerStack = \GuzzleHttp\HandlerStack::create($mock);
        $handlerStack->push($historyMiddleware);

        $client = new Client($this->validAccountSid, $this->validAuthToken);

        $rbl = $client->rbl;
        $hosts = $rbl->hosts;

        $reflection = new \ReflectionClass($hosts);
        $prop = $reflection->getProperty('http_client');
        $prop->setAccessible(true);
        $prop->setValue($hosts, new \GuzzleHttp\Client([
            'handler' => $handlerStack,
            'base_uri' => 'https://api.generatorlabs.com/4.0/',
        ]));

        $hosts->create([
            'name' => 'Test Host',
            'host' => '1.2.3.4',
            'contact_group' => 'CG11111111111111111111111111111111'
        ]);

        $this->assertCount(1, $history);

        $body = (string) $history[0]['request']->getBody();
        parse_str($body, $params);

        $this->assertEquals(
            'CG11111111111111111111111111111111',
            $params['contact_group']
        );
    }
}
