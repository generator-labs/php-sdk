<?php

/**
 * Example: Proper error handling and retry behavior
 */

require_once __DIR__ . '/../vendor/autoload.php';

use GeneratorLabs\Client;
use GeneratorLabs\Exception;

$accountSid = getenv('GENERATOR_LABS_ACCOUNT_SID');
$authToken = getenv('GENERATOR_LABS_AUTH_TOKEN');

if (!$accountSid || !$authToken) {
    die("Error: Set GENERATOR_LABS_ACCOUNT_SID and GENERATOR_LABS_AUTH_TOKEN environment variables\n");
}

try {
    // Initialize client with custom configuration
    $client = new Client($accountSid, $authToken, [
        'timeout' => 45,           // 45 second timeout
        'connect_timeout' => 10,   // 10 second connection timeout
        'max_retries' => 5,        // 5 retry attempts
        'retry_backoff' => 2       // 2x backoff multiplier
    ]);

    echo "=== Example 1: Handling API errors ===\n";
    try {
        // Try to get a non-existent host
        $client->rbl->hosts->get(999999);
    } catch (Exception $e) {
        echo "Caught error: " . $e->getMessage() . "\n";
        echo "This is expected for a non-existent resource\n\n";
    }

    echo "=== Example 2: Invalid credentials ===\n";
    try {
        $badClient = new Client('INVALID', $authToken);
    } catch (Exception $e) {
        echo "Caught error: " . $e->getMessage() . "\n";
        echo "Credential validation works!\n\n";
    }

    echo "=== Example 3: Network resilience ===\n";
    // The SDK automatically retries on:
    // - Connection errors
    // - 5xx server errors
    // - 429 rate limit errors
    // With exponential backoff: 1s, 2s, 4s, 8s, 16s

    $result = $client->rbl->check->get(['ip' => '1.1.1.1']);
    echo "Request succeeded (with automatic retries if needed)\n";

    echo "\n=== Example 4: Graceful degradation ===\n";
    try {
        $hosts = $client->rbl->hosts->get();
        echo "Successfully retrieved " . count($hosts['hosts'] ?? []) . " hosts\n";
    } catch (Exception $e) {
        // Log error and continue with cached/default data
        error_log("API error: " . $e->getMessage());
        echo "Using cached data due to API error\n";
        $hosts = ['hosts' => []];  // Fallback to empty array
    }

} catch (Exception $e) {
    echo "Fatal error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nAll examples completed!\n";
