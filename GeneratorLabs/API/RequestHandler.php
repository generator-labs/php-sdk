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
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

trait RequestHandler
{
    private \GeneratorLabs\Client $m_client;
    private ?GuzzleClient $http_client = null;

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

            // Retry on 5xx server errors
            if ($response && $response->getStatusCode() >= 500) {
                return true;
            }

            // Retry on 429 Too Many Requests
            if ($response && $response->getStatusCode() === 429) {
                return true;
            }

            return false;
        };
    }

    //
    // calculate retry delay with exponential backoff
    //
    private function retryDelay(): callable
    {
        $backoffFactor = $this->m_client->config('retry_backoff') ?? 1;

        return function (int $numberOfRetries) use ($backoffFactor): int {
            // Exponential backoff with configurable factor
            return (int)(1000 * $backoffFactor * (2 ** ($numberOfRetries - 1)));
        };
    }

    //
    // make the actual request
    //
    private function request(string $_type, string $_action, ?array $_args = null): array
    {
        $url = $_action . '.json';

        try {
            $options = [];

            // Handle request based on method
            if ($_type === 'GET' && !is_null($_args)) {
                $options['query'] = $_args;
            } elseif (in_array($_type, ['POST', 'PUT', 'DELETE']) && !is_null($_args)) {
                $options['form_params'] = $_args;
            }

            // Make the request
            $response = $this->http_client->request($_type, $url, $options);

            // Get response body
            $body = (string) $response->getBody();

            // Check for empty response
            if (empty($body)) {
                throw new Exception('Empty response from Generator Labs API');
            }

            // Decode JSON
            $data = json_decode($body, true);
            if (is_null($data)) {
                throw new Exception('Failed to decode JSON response from Generator Labs API');
            }

            // Check for API errors (v4.0 format)
            if (isset($data['success']) && $data['success'] === false) {
                $message = $data['error']['message'] ?? $data['message'] ?? 'Unknown error';
                throw new Exception('Generator Labs API error: ' . $message);
            }

            // Check HTTP status code
            $statusCode = $response->getStatusCode();
            if ($statusCode >= 400) {
                $message = $data['error']['message'] ?? $data['message'] ?? "HTTP {$statusCode} error";
                throw new Exception('Generator Labs API error: ' . $message);
            }

            return $data;

        } catch (RequestException $e) {
            // Guzzle exception
            $message = $e->getMessage();
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $body = (string) $response->getBody();
                $data = json_decode($body, true);
                if ($data && isset($data['error']['message'])) {
                    $message = $data['error']['message'];
                } elseif ($data && isset($data['message'])) {
                    $message = $data['message'];
                }
            }
            throw new Exception('Generator Labs API request failed: ' . $message);

        } catch (\Exception $e) {
            if ($e instanceof Exception) {
                throw $e;
            }
            throw new Exception('Generator Labs API request failed: ' . $e->getMessage());
        }
    }

    //
    // make a GET request to the API
    //
    protected function _get(string $_action, ?array $_args = null): array
    {
        return $this->request('GET', $_action, $_args);
    }

    //
    // make a POST request to the API
    //
    protected function _post(string $_action, ?array $_args = null): array
    {
        return $this->request('POST', $_action, $_args);
    }

    //
    // make a PUT request to the API
    //
    protected function _put(string $_action, ?array $_args = null): array
    {
        return $this->request('PUT', $_action, $_args);
    }

    //
    // make a DELETE request to the API
    //
    protected function _delete(string $_action, ?array $_args = null): array
    {
        return $this->request('DELETE', $_action, $_args);
    }
}
