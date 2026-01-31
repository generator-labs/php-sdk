<?php

/**
 * Example: Certificate monitoring - list errors, manage monitors and profiles
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

    // ===================================================================
    // Certificate Errors
    // ===================================================================
    echo "=== Listing Certificate Errors ===\n";
    $errors = $client->cert->errors->get();
    echo "Total errors: " . count($errors['errors'] ?? []) . "\n\n";

    foreach ($errors['errors'] ?? [] as $error) {
        echo "Error ID: {$error['id']}\n";
        echo "  Monitor: {$error['monitor_name']}\n";
        echo "  Type: {$error['error_type']}\n";
        echo "  Message: {$error['message']}\n\n";
    }

    // ===================================================================
    // Certificate Profiles
    // ===================================================================
    echo "=== Managing Certificate Profiles ===\n";

    // List all profiles
    $profiles = $client->cert->profiles->get();
    echo "Total profiles: " . count($profiles['profiles'] ?? []) . "\n";

    // Create a new profile
    echo "\n=== Creating a new certificate profile ===\n";
    $newProfile = $client->cert->profiles->create([
        'name' => 'Example Certificate Profile',
        'expiration_warning_days' => 30,
        'expiration_critical_days' => 7,
        'check_self_signed' => true,
        'check_hostname_mismatch' => true
    ]);
    echo "Created profile ID: {$newProfile['profile']['id']}\n";
    $profileId = $newProfile['profile']['id'];

    // Get specific profile
    echo "\n=== Getting specific profile ===\n";
    $profile = $client->cert->profiles->get($profileId);
    echo "Profile name: {$profile['profile']['name']}\n";
    echo "Expiration warning days: {$profile['profile']['expiration_warning_days']}\n";

    // Update profile
    echo "\n=== Updating profile ===\n";
    $updatedProfile = $client->cert->profiles->update($profileId, [
        'expiration_warning_days' => 45
    ]);
    echo "Updated profile warning days to 45\n";

    // ===================================================================
    // Certificate Monitors
    // ===================================================================
    echo "\n=== Managing Certificate Monitors ===\n";

    // List all monitors
    $monitors = $client->cert->monitors->get();
    echo "Total monitors: " . count($monitors['monitors'] ?? []) . "\n";

    // Create a new HTTPS monitor
    echo "\n=== Creating HTTPS certificate monitor ===\n";
    $httpsMonitor = $client->cert->monitors->create([
        'name' => 'Example HTTPS Monitor',
        'hostname' => 'example.com',
        'port' => 443,
        'protocol' => 'https',
        'cert_profile' => $profileId,
        'contact_group' => 'CG4f3e2d1c0b9a8776655443322110fed'  // Use your contact group ID
    ]);
    echo "Created HTTPS monitor ID: {$httpsMonitor['monitor']['id']}\n";
    $httpsMonitorId = $httpsMonitor['monitor']['id'];

    // Create a mail server monitor (SMTPS)
    echo "\n=== Creating SMTPS certificate monitor ===\n";
    $smtpsMonitor = $client->cert->monitors->create([
        'name' => 'Example Mail Server Monitor',
        'hostname' => 'mail.example.com',
        'port' => 465,
        'protocol' => 'smtps',
        'cert_profile' => $profileId,
        'contact_group' => 'CG4f3e2d1c0b9a8776655443322110fed'
    ]);
    echo "Created SMTPS monitor ID: {$smtpsMonitor['monitor']['id']}\n";
    $smtpsMonitorId = $smtpsMonitor['monitor']['id'];

    // Get specific monitor
    echo "\n=== Getting specific monitor ===\n";
    $monitor = $client->cert->monitors->get($httpsMonitorId);
    echo "Monitor name: {$monitor['monitor']['name']}\n";
    echo "Hostname: {$monitor['monitor']['hostname']}\n";
    echo "Protocol: {$monitor['monitor']['protocol']}\n";
    echo "Status: {$monitor['monitor']['status']}\n";

    // Update monitor
    echo "\n=== Updating monitor ===\n";
    $updatedMonitor = $client->cert->monitors->update($httpsMonitorId, [
        'name' => 'Updated HTTPS Monitor Name'
    ]);
    echo "Updated monitor name\n";

    // Pause monitoring
    echo "\n=== Pausing monitor ===\n";
    $client->cert->monitors->pause($httpsMonitorId);
    echo "Paused monitor ID: {$httpsMonitorId}\n";

    // Resume monitoring
    echo "\n=== Resuming monitor ===\n";
    $client->cert->monitors->resume($httpsMonitorId);
    echo "Resumed monitor ID: {$httpsMonitorId}\n";

    // ===================================================================
    // Cleanup
    // ===================================================================
    echo "\n=== Cleaning up - Deleting created resources ===\n";

    // Delete monitors
    $client->cert->monitors->delete($httpsMonitorId);
    echo "Deleted HTTPS monitor ID: {$httpsMonitorId}\n";

    $client->cert->monitors->delete($smtpsMonitorId);
    echo "Deleted SMTPS monitor ID: {$smtpsMonitorId}\n";

    // Delete profile
    $client->cert->profiles->delete($profileId);
    echo "Deleted profile ID: {$profileId}\n";

    echo "\n=== Certificate Monitoring Example Complete ===\n";

} catch (\GeneratorLabs\Exception $e) {
    echo "API Error: " . $e->getMessage() . "\n";
    exit(1);
}
