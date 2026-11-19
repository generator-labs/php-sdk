<?php declare(strict_types=1);

namespace GeneratorLabs\Tests;

use PHPUnit\Framework\TestCase;
use GeneratorLabs\Client;
use GeneratorLabs\Exception;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;

final class RequestOptionsTest extends TestCase
{
    private Client $client;
    private array $history = [];

    protected function setUp(): void
    {
        $this->client = new Client(
            'AC12345678901234567890123456789012',
            '0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef',
            ['retry_backoff' => 0.001]
        );
        $this->history = [];
    }

    //
    // answer the resource's requests with the given responses, through the SDK's own retry
    // middleware, recording every attempt
    //
    private function mock(object $_resource, array $_responses): void
    {
        $reflection = new \ReflectionClass($_resource);

        $stack = HandlerStack::create(new MockHandler($_responses));
        $stack->push(Middleware::retry(
            $reflection->getMethod('retryDecider')->invoke($_resource),
            $reflection->getMethod('retryDelay')->invoke($_resource)
        ));
        $stack->push(Middleware::history($this->history));

        $reflection->getProperty('http_client')->setValue($_resource, new GuzzleClient([
            'handler' => $stack,
            'base_uri' => 'https://api.generatorlabs.com/4.0/',
            'http_errors' => false,
        ]));
    }

    private function ok(array $_headers = [], array $_body = []): Response
    {
        return new Response(200, $_headers, json_encode(array_merge([
            'status_code' => 200,
            'status_message' => 'OK'
        ], $_body)));
    }

    private function error(int $_status, array $_headers = []): Response
    {
        return new Response($_status, $_headers, json_encode([
            'status_code' => $_status,
            'status_message' => 'Request failed.'
        ]));
    }

    private function header(int $_index, string $_name): string
    {
        return $this->history[$_index]['request']->getHeaderLine($_name);
    }

    private function path(int $_index): string
    {
        return $this->history[$_index]['request']->getMethod() . ' ' .
            $this->history[$_index]['request']->getUri()->getPath();
    }

    public function testSameIdempotencyKeyOnEveryRetry(): void
    {
        $hosts = $this->client->rbl->hosts;
        $this->mock($hosts, [$this->error(500), $this->ok()]);

        $hosts->create(['name' => 'Test', 'host' => '192.0.2.1']);

        $this->assertCount(2, $this->history);
        $this->assertSame(32, strlen($this->header(0, 'Idempotency-Key')));
        $this->assertSame($this->header(0, 'Idempotency-Key'), $this->header(1, 'Idempotency-Key'));
    }

    public function testBodyThatIsNotAJsonObjectIsAnError(): void
    {
        $hosts = $this->client->rbl->hosts;
        $this->mock($hosts, [new Response(200, [], '"text"')]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Failed to decode JSON response');

        $hosts->get();
    }

    public function testNewIdempotencyKeyForEachCall(): void
    {
        $hosts = $this->client->rbl->hosts;
        $this->mock($hosts, [$this->ok(), $this->ok()]);

        $hosts->create(['name' => 'One', 'host' => '192.0.2.1']);
        $hosts->create(['name' => 'Two', 'host' => '192.0.2.2']);

        $this->assertNotSame($this->header(0, 'Idempotency-Key'), $this->header(1, 'Idempotency-Key'));
    }

    public function testCallerIdempotencyKeyWins(): void
    {
        $check = $this->client->rbl->check;
        $this->mock($check, [$this->ok()]);

        $check->start(['host' => '192.0.2.1'], ['idempotency_key' => 'my-key']);

        $this->assertSame('my-key', $this->header(0, 'Idempotency-Key'));
    }

    public function testIdempotencyKeyOnTheEndpointsThatAcceptOne(): void
    {
        $contacts = $this->client->contact->contacts;
        $monitors = $this->client->cert->monitors;
        $this->mock($contacts, [$this->ok(), $this->ok()]);
        $this->mock($monitors, [$this->ok(), $this->ok()]);

        $contacts->resend('COx');
        $contacts->confirm('COx', ['authcode' => '1234']);
        $monitors->delete('CMx');
        $monitors->check('CMx');

        $this->assertCount(4, $this->history);
        foreach (array_keys($this->history) as $index) {
            $this->assertNotSame('', $this->header($index, 'Idempotency-Key'));
        }
    }

    public function testNoIdempotencyKeyOnOtherWrites(): void
    {
        $hosts = $this->client->rbl->hosts;
        $this->mock($hosts, [$this->ok(), $this->ok(), $this->ok()]);

        $hosts->update('HTx', ['name' => 'Test']);
        $hosts->pause('HTx');
        $hosts->delete('HTx');

        foreach (array_keys($this->history) as $index) {
            $this->assertSame('', $this->header($index, 'Idempotency-Key'));
        }
    }

    public function testReplayed(): void
    {
        $hosts = $this->client->rbl->hosts;
        $this->mock($hosts, [$this->ok(['Idempotent-Replayed' => 'true']), $this->ok()]);

        $this->assertTrue($hosts->create(['name' => 'Test'])->replayed);
        $this->assertFalse($hosts->create(['name' => 'Test'])->replayed);
    }

    public function testRequestIdSentAndReturned(): void
    {
        $hosts = $this->client->rbl->hosts;
        $this->mock($hosts, [$this->ok(['X-Request-Id' => 'req-1'])]);

        $response = $hosts->get(null, ['request_id' => 'req-1']);

        $this->assertSame('req-1', $this->header(0, 'X-Request-Id'));
        $this->assertSame('req-1', $response->request_id);
    }

    public function testRequestIdOnAnError(): void
    {
        $hosts = $this->client->rbl->hosts;
        $this->mock($hosts, [$this->error(404, ['X-Request-Id' => 'req-2'])]);

        try {
            $hosts->get('HTx');
            $this->fail('expected an exception');
        } catch (Exception $e) {
            $this->assertSame(404, $e->getCode());
            $this->assertSame('req-2', $e->request_id);
        }
    }

    public function testEtagAndNotModified(): void
    {
        $hosts = $this->client->rbl->hosts;
        $this->mock($hosts, [
            $this->ok(['ETag' => 'W/"abc"'], ['data' => []]),
            new Response(304, ['ETag' => 'W/"abc"'])
        ]);

        $first  = $hosts->get();
        $second = $hosts->get(null, ['if_none_match' => $first->etag]);

        $this->assertSame('W/"abc"', $first->etag);
        $this->assertFalse($first->not_modified);
        $this->assertSame('W/"abc"', $this->header(1, 'If-None-Match'));
        $this->assertTrue($second->not_modified);
        $this->assertSame([], $second->toArray());
    }

    public function testFieldsOnASingleGet(): void
    {
        $hosts = $this->client->rbl->hosts;
        $this->mock($hosts, [$this->ok()]);

        $hosts->get('HTx', ['fields' => ['id', 'host']]);

        $this->assertSame('GET /4.0/rbl/hosts/HTx.json', $this->path(0));
        $this->assertSame('fields=id%2Chost', $this->history[0]['request']->getUri()->getQuery());
    }

    public function testFieldsOnAList(): void
    {
        $hosts = $this->client->rbl->hosts;
        $this->mock($hosts, [$this->ok()]);

        $hosts->get(['page' => 2], ['fields' => 'id,host']);

        $this->assertSame('page=2&fields=id%2Chost', $this->history[0]['request']->getUri()->getQuery());
    }

    public function testUnknownOption(): void
    {
        $hosts = $this->client->rbl->hosts;
        $this->mock($hosts, [$this->ok()]);

        $this->expectException(Exception::class);

        $hosts->get(null, ['bogus' => 1]);
    }

    public function testShortRetryAfterIsRetried(): void
    {
        $hosts = $this->client->rbl->hosts;
        $this->mock($hosts, [$this->error(429, ['Retry-After' => '1']), $this->ok()]);

        $hosts->get();

        $this->assertCount(2, $this->history);
    }

    public function testLongRetryAfterIsReturnedAsTheError(): void
    {
        $monitors = $this->client->cert->monitors;
        $this->mock($monitors, [$this->error(429, ['Retry-After' => '3600'])]);

        try {
            $monitors->check('CMx');
            $this->fail('expected an exception');
        } catch (Exception $e) {
            $this->assertSame(429, $e->getCode());
            $this->assertCount(1, $this->history);
        }
    }

    public function testAccountGet(): void
    {
        $account = $this->client->account;
        $this->mock($account, [$this->ok([], ['data' => ['balance' => 80.1226]])]);

        $response = $account->get();

        $this->assertSame('GET /4.0/account.json', $this->path(0));
        $this->assertSame(80.1226, $response['data']['balance']);
    }

    public function testAccountBalance(): void
    {
        $account = $this->client->account;
        $this->mock($account, [new Response(200, ['Content-Type' => 'text/plain'], "80.1226\n")]);

        $this->assertSame(80.1226, $account->balance());
        $this->assertSame('GET /4.0/account/balance.json', $this->path(0));
    }

    public function testAccountBalanceError(): void
    {
        $account = $this->client->account;
        $this->mock($account, [new Response(401, ['Content-Type' => 'text/plain'], "-1\n")]);

        try {
            $account->balance();
            $this->fail('expected an exception');
        } catch (Exception $e) {
            $this->assertSame(401, $e->getCode());
        }
    }

    public function testAccountUsage(): void
    {
        $account = $this->client->account;
        $this->mock($account, [$this->ok()]);

        $account->usage(['months' => 3]);

        $this->assertSame('GET /4.0/account/usage.json', $this->path(0));
        $this->assertSame('months=3', $this->history[0]['request']->getUri()->getQuery());
    }

    public function testAccountPauseAndResumeChecks(): void
    {
        $account = $this->client->account;
        $this->mock($account, [$this->ok(), $this->ok()]);

        $account->pauseChecks();
        $account->resumeChecks();

        $this->assertSame('POST /4.0/account/checks/pause.json', $this->path(0));
        $this->assertSame('POST /4.0/account/checks/resume.json', $this->path(1));
    }

    public function testCertFindings(): void
    {
        $findings = $this->client->cert->findings;
        $this->mock($findings, [$this->ok()]);

        $findings->get(['dismissed' => 1]);

        $this->assertSame('GET /4.0/cert/findings.json', $this->path(0));
        $this->assertSame('dismissed=1', $this->history[0]['request']->getUri()->getQuery());
    }

    public function testCertFindingsGetAll(): void
    {
        $findings = $this->client->cert->findings;
        $this->mock($findings, [
            $this->ok([], ['total_pages' => 2, 'data' => [['id' => 'CM1']]]),
            $this->ok([], ['total_pages' => 2, 'data' => [['id' => 'CM2']]])
        ]);

        $this->assertSame([['id' => 'CM1'], ['id' => 'CM2']], $findings->getAll());
    }

    public function testCertFindingsDismissAndRestore(): void
    {
        $findings = $this->client->cert->findings;
        $this->mock($findings, [$this->ok(), $this->ok()]);

        $findings->dismiss(['finding_id' => ['CERT_CAA01', 'CERT_CAA02'], 'monitors' => ['CM1', 'CM2']]);
        $findings->restore(['finding_id' => 'CERT_CAA01']);

        $this->assertSame('POST /4.0/cert/findings/dismiss.json', $this->path(0));
        parse_str((string) $this->history[0]['request']->getBody(), $params);
        $this->assertSame(['finding_id' => 'CERT_CAA01,CERT_CAA02', 'monitors' => 'CM1,CM2'], $params);

        $this->assertSame('POST /4.0/cert/findings/restore.json', $this->path(1));
        parse_str((string) $this->history[1]['request']->getBody(), $params);
        $this->assertSame(['finding_id' => 'CERT_CAA01'], $params);
    }

    public function testCertMonitorCheck(): void
    {
        $monitors = $this->client->cert->monitors;
        $this->mock($monitors, [$this->ok()]);

        $monitors->check('CMx');

        $this->assertSame('POST /4.0/cert/monitors/CMx/check.json', $this->path(0));
    }

    public function testCertErrorsCount(): void
    {
        $errors = $this->client->cert->errors;
        $this->mock($errors, [new Response(200, ['Content-Type' => 'text/plain'], "3\n")]);

        $this->assertSame(3, $errors->count());
        $this->assertSame('GET /4.0/cert/errors/count.json', $this->path(0));
    }
}
