<?php

/**
 * Example: Manage monitored hosts (create, list, update, delete)
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

    // List all hosts
    echo "=== Listing all monitored hosts ===\n";
    $hosts = $client->rbl->hosts->get();
    echo "Total hosts: " . count($hosts['hosts'] ?? []) . "\n\n";

    foreach ($hosts['hosts'] ?? [] as $host) {
        echo "ID: {$host['id']}, IP: {$host['ip']}, Description: {$host['description']}\n";
    }

    // Create a new host
    echo "\n=== Creating a new host ===\n";
    $newHost = $client->rbl->hosts->create([
        'ip' => '203.0.113.10',
        'description' => 'Example host from PHP SDK',
        'profile_id' => 1  // Use your profile ID
    ]);
    echo "Created host ID: {$newHost['host']['id']}\n";
    $hostId = $newHost['host']['id'];

    // Get specific host
    echo "\n=== Getting specific host ===\n";
    $host = $client->rbl->hosts->get($hostId);
    echo "Host details:\n";
    print_r($host);

    // Update host
    echo "\n=== Updating host ===\n";
    $updatedHost = $client->rbl->hosts->update($hostId, [
        'description' => 'Updated description from PHP SDK'
    ]);
    echo "Updated host description\n";

    // Delete host
    echo "\n=== Deleting host ===\n";
    $client->rbl->hosts->delete($hostId);
    echo "Deleted host ID: {$hostId}\n";

} catch (\GeneratorLabs\Exception $e) {
    echo "API Error: " . $e->getMessage() . "\n";
    exit(1);
}
