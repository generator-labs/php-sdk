<?php

/**
 * Example: Paginate through large result sets
 */

require_once __DIR__ . '/../vendor/autoload.php';

use GeneratorLabs\Client;

$accountSid = getenv('GENERATOR_LABS_ACCOUNT_SID');
$authToken = getenv('GENERATOR_LABS_AUTH_TOKEN');

if (!$accountSid || !$authToken) {
    die("Error: Set GENERATOR_LABS_ACCOUNT_SID and GENERATOR_LABS_AUTH_TOKEN environment variables\n");
}

try {
    $client = new Client($accountSid, $authToken);

    echo "=== Fetching all hosts with pagination ===\n";

    $allHosts = [];
    $page = 1;
    $pageSize = 50;

    do {
        echo "Fetching page {$page}...\n";

        $response = $client->rbl->hosts->get([
            'page' => $page,
            'page_size' => $pageSize
        ]);

        $hosts = $response['hosts'] ?? [];
        $allHosts = array_merge($allHosts, $hosts);

        echo "  Retrieved " . count($hosts) . " hosts\n";

        // Check if there are more pages
        $hasMore = $response['has_more'] ?? false;
        $page++;

    } while ($hasMore);

    echo "\nTotal hosts retrieved: " . count($allHosts) . "\n";

    // Alternative: Use the built-in pagination helper
    echo "\n=== Using pagination helper ===\n";

    $allHostsHelper = $client->rbl->hosts->getAll(['page_size' => 50]);
    echo "Total hosts via helper: " . count($allHostsHelper) . "\n";

} catch (\GeneratorLabs\Exception $e) {
    echo "API Error: " . $e->getMessage() . "\n";
    exit(1);
}
