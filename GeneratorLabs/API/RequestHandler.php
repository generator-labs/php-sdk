<?php declare(strict_types=1);

//
// This file is part of the Generator Labs PHP SDK package.
//
// (c) Generator Labs <support@generatorlabs.com>
//
// For the full copyright and license information, please view the LICENSE
// file that was distributed with this source code.
//

namespace GeneratorLabs\API;

use GeneratorLabs\Exception;
use GeneratorLabs\RateLimitInfo;
use GeneratorLabs\Response as ApiResponse;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

/**
 * @phpstan-import-type RequestOptions from \GeneratorLabs\Client
 */
trait RequestHandler
{
    private \GeneratorLabs\Client $m_client;
    private GuzzleClient $http_client;

    //
    // init a new object
    //
    protected function init(\GeneratorLabs\Client $_client): void
    {
        $this->m_client = $_client;
        $this->initHttpClient();
    }

    //
    // initialize Guzzle HTTP client with retry middleware
    //
    private function initHttpClient(): void
    {
        // Create handler stack with retry middleware
        $handlerStack = HandlerStack::create();

        // Add retry middleware
        $handlerStack->push(Middleware::retry(
            $this->retryDecider(),
            $this->retryDelay()
        ));

        // Get configuration from client
        $timeout = $this->m_client->config('timeout') ?? 30.0;
        $connectTimeout = $this->m_client->config('connect_timeout') ?? 5.0;

        // Create Guzzle client with configuration
        $this->http_client = new GuzzleClient([
            'base_uri' => $this->m_client->url(),
            'timeout' => $timeout,
            'connect_timeout' => $connectTimeout,
            'handler' => $handlerStack,
            'auth' => [
                $this->m_client->account_sid(),
                $this->m_client->api_token()
            ],
            'headers' => [
                'User-Agent' => 'GeneratorLabs-PHP/' . \GeneratorLabs\Client::VERSION,
                'Accept' => 'application/json',
            ],
            'http_errors' => false,  // Handle errors manually
        ]);
    }

    //
    // decide whether to retry a request
    //
    private function retryDecider(): callable
    {
        $maxRetries = $this->m_client->config('max_retries') ?? 3;

        return function (
            int $retries,
            Request $request,
            ?Response $response = null,
            ?\Throwable $exception = null
        ) use ($maxRetries): bool {
            // Don't retry after max attempts
            if ($retries >= $maxRetries) {
                return false;
            }

            // Retry connection errors
            if ($exception instanceof ConnectException) {
                return true;
            }

            // Retry on 5xx server errors and 429 Too Many Requests, unless the Retry-After
            // header asks for a longer wait than MAX_RETRY_AFTER: that response is returned
            // as the error instead
            if ($response && ($response->getStatusCode() >= 500 || $response->getStatusCode() === 429)) {
                return (int) $response->getHeaderLine('Retry-After') <= \GeneratorLabs\Client::MAX_RETRY_AFTER;
            }

            return false;
        };
    }

    //
    // calculate retry delay; respect Retry-After header, fall back to exponential backoff
    //
    private function retryDelay(): callable
    {
        $backoffFactor = $this->m_client->config('retry_backoff') ?? 1;

        return function (int $numberOfRetries, ?Response $response = null) use ($backoffFactor): int {

            // Use Retry-After header when present (rate limit responses)
            if ($response !== null && $response->hasHeader('Retry-After')) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                if ($retryAfter > 0) {
                    return $retryAfter * 1000;
                }
            }

            // Exponential backoff with configurable factor
            return (int)(1000 * $backoffFactor * (2 ** ($numberOfRetries - 1)));
        };
    }

    //
    // send a request to the API; $_idempotent is set for an endpoint that accepts an
    // Idempotency-Key, and one is generated when the caller gave none
    //
    /**
     * @param array<string, mixed>|null $_args
     * @param RequestOptions $_options
     */
    private function send(
        string $_type,
        string $_action,
        ?array $_args = null,
        array $_options = [],
        bool $_idempotent = false
    ): ResponseInterface {
        $unknown = array_diff(array_keys($_options), \GeneratorLabs\Client::REQUEST_OPTIONS);
        if (count($unknown) > 0) {
            throw new Exception('Unknown request option: ' . implode(', ', $unknown));
        }

        // The headers are set once per call, so every retry of the call sends the same
        // Idempotency-Key and the API answers a repeat with the first result
        $headers = [];
        if (isset($_options['idempotency_key'])) {
            $headers['Idempotency-Key'] = (string) $_options['idempotency_key'];
        } elseif ($_idempotent) {
            $headers['Idempotency-Key'] = bin2hex(random_bytes(16));
        }
        if (isset($_options['request_id'])) {
            $headers['X-Request-Id'] = (string) $_options['request_id'];
        }
        if (isset($_options['if_none_match'])) {
            $headers['If-None-Match'] = (string) $_options['if_none_match'];
        }

        // Field selection applies to GET requests
        if ($_type === 'GET' && isset($_options['fields'])) {
            $_args = array_merge($_args ?? [], ['fields' => $_options['fields']]);
        }

        try {
            $options = ['headers' => $headers];

            // Convert array values to comma-separated strings for form encoding
            if (!is_null($_args)) {
                foreach ($_args as $key => $value) {
                    if (is_array($value)) {
                        $_args[$key] = implode(',', $value);
                    }
                }
            }

            // Handle request based on method
            if ($_type === 'GET' && !is_null($_args)) {
                $options['query'] = $_args;
            } elseif (in_array($_type, ['POST', 'PUT', 'DELETE']) && !is_null($_args)) {
                $options['form_params'] = $_args;
            }

            // Make the request
            return $this->http_client->request($_type, $_action . '.json', $options);

        } catch (RequestException $e) {
            // Guzzle exception
            $message = $e->getMessage();
            $response = $e->getResponse();
            if (!is_null($response)) {
                $data = json_decode((string) $response->getBody(), true);
                if (is_array($data) && isset($data['status_message'])) {
                    $message = $data['status_message'];
                }
            }
            throw new Exception('Generator Labs API request failed: ' . $message);

        } catch (\Exception $e) {
            throw new Exception('Generator Labs API request failed: ' . $e->getMessage());
        }
    }

    //
    // make the actual request
    //
    /**
     * @param array<string, mixed>|null $_args
     * @param RequestOptions $_options
     */
    private function request(
        string $_type,
        string $_action,
        ?array $_args = null,
        array $_options = [],
        bool $_idempotent = false
    ): ApiResponse {
        $response   = $this->send($_type, $_action, $_args, $_options, $_idempotent);
        $statusCode = $response->getStatusCode();
        $request_id = $response->hasHeader('X-Request-Id') ? $response->getHeaderLine('X-Request-Id') : null;
        $etag       = $response->hasHeader('ETag') ? $response->getHeaderLine('ETag') : null;

        //
        // parse rate limit headers
        //
        $rate_limit = null;
        if ($response->hasHeader('RateLimit-Limit')) {
            $rate_limit = new RateLimitInfo(
                $response->getHeaderLine('RateLimit-Limit'),
                (int) $response->getHeaderLine('RateLimit-Remaining'),
                (int) $response->getHeaderLine('RateLimit-Reset')
            );
        }

        // 304 Not Modified has no body: the copy the caller's ETag came from is still current
        if ($statusCode === 304) {
            return new ApiResponse([], $rate_limit, $request_id, $etag, false, true);
        }

        // Get response body
        $body = (string) $response->getBody();

        // Check for empty response
        if (empty($body)) {
            throw new Exception('Empty response from Generator Labs API', 0, $request_id);
        }

        // Decode JSON; every API answer is a JSON object
        $data = json_decode($body, true);
        if (!is_array($data)) {
            throw new Exception('Failed to decode JSON response from Generator Labs API', 0, $request_id);
        }

        // Determine success vs failure from the API status_code (which mirrors the HTTP status),
        // and surface the API status_message.
        $apiCode = isset($data['status_code']) ? (int) $data['status_code'] : $statusCode;
        if ($apiCode >= 400 || $statusCode >= 400) {
            $message = $data['status_message'] ?? "HTTP {$statusCode} error";
            throw new Exception('Generator Labs API error: ' . $message, $apiCode, $request_id);
        }

        return new ApiResponse(
            $data,
            $rate_limit,
            $request_id,
            $etag,
            $response->getHeaderLine('Idempotent-Replayed') === 'true'
        );
    }

    //
    // make a GET request to the API
    //
    /**
     * @param array<string, mixed>|null $_args
     * @param RequestOptions $_options
     */
    protected function _get(string $_action, ?array $_args = null, array $_options = []): ApiResponse
    {
        return $this->request('GET', $_action, $_args, $_options);
    }

    //
    // make a GET request to an endpoint that answers in plain text, and return the body
    //
    /** @param RequestOptions $_options */
    protected function _getPlain(string $_action, array $_options = []): string
    {
        $response = $this->send('GET', $_action, null, $_options);

        if ($response->getStatusCode() >= 400) {
            throw new Exception(
                "Generator Labs API error: HTTP {$response->getStatusCode()} error",
                $response->getStatusCode(),
                $response->hasHeader('X-Request-Id') ? $response->getHeaderLine('X-Request-Id') : null
            );
        }

        return trim((string) $response->getBody());
    }

    //
    // make a POST request to the API
    //
    /**
     * @param array<string, mixed>|null $_args
     * @param RequestOptions $_options
     */
    protected function _post(
        string $_action,
        ?array $_args = null,
        array $_options = [],
        bool $_idempotent = false
    ): ApiResponse {
        return $this->request('POST', $_action, $_args, $_options, $_idempotent);
    }

    //
    // make a PUT request to the API
    //
    /**
     * @param array<string, mixed>|null $_args
     * @param RequestOptions $_options
     */
    protected function _put(string $_action, ?array $_args = null, array $_options = []): ApiResponse
    {
        return $this->request('PUT', $_action, $_args, $_options);
    }

    //
    // make a DELETE request to the API
    //
    /**
     * @param array<string, mixed>|null $_args
     * @param RequestOptions $_options
     */
    protected function _delete(
        string $_action,
        ?array $_args = null,
        array $_options = [],
        bool $_idempotent = false
    ): ApiResponse {
        return $this->request('DELETE', $_action, $_args, $_options, $_idempotent);
    }
}
