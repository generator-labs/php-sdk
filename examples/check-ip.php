<?php

/**
 * Example: Check if an IP address is listed on any RBLs
 */

require_once __DIR__ . '/../vendor/autoload.php';

use GeneratorLabs\Client;

// Initialize client with credentials
$accountSid = getenv('GENERATOR_LABS_ACCOUNT_SID');
$authToken = getenv('GENERATOR_LABS_AUTH_TOKEN');

if (!$accountSid || !$authToken) {
    die("Error: Set GENERATOR_LABS_ACCOUNT_SID and GENERATOR_LABS_AUTH_TOKEN environment variables\n");
}

try {
    $client = new Client($accountSid, $authToken);

    // Check a single IP address
    $ip = '8.8.8.8';
    echo "Checking IP: {$ip}\n";

    $result = $client->rbl->check->get(['ip' => $ip]);

    echo "Results:\n";
    print_r($result);

    // Check if IP is listed
    if (isset($result['listed']) && $result['listed']) {
        echo "\nWARNING: IP {$ip} is listed on one or more RBLs!\n";
        if (isset($result['listings'])) {
            echo "Listed on: " . count($result['listings']) . " RBL(s)\n";
        }
    } else {
        echo "\nIP {$ip} is clean - not listed on any RBLs\n";
    }

} catch (\GeneratorLabs\Exception $e) {
    echo "API Error: " . $e->getMessage() . "\n";
    exit(1);
}
