<?php declare(strict_types=1);

require 'vendor/autoload.php';

// Initialize client
$client = new GeneratorLabs\Client('AC12345678901234567890123456789012', '0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef');

try {
    // ===== RBL Hosts =====

    // List all hosts
    $hosts = $client->rbl->hosts->get(['page_size' => 10]);

    // Get a single host
    $host = $client->rbl->hosts->get('HT1a2b3c4d5e6f7890abcdef1234567890');

    // Create a host
    $newHost = $client->rbl->hosts->create([
        'name' => 'My Mail Server',
        'host' => '192.168.1.100',
        'type' => 'rbl',
        'rbl_profile' => 'RP9f8e7d6c5b4a3210fedcba0987654321',
        'contact_group' => 'CG4f3e2d1c0b9a8776655443322110fed'
    ]);

    // Update a host
    $client->rbl->hosts->update('HT1a2b3c4d5e6f7890abcdef1234567890', [
        'name' => 'Updated Server Name'
    ]);

    // Pause a host
    $client->rbl->hosts->pause('HT1a2b3c4d5e6f7890abcdef1234567890');

    // Resume a host
    $client->rbl->hosts->resume('HT1a2b3c4d5e6f7890abcdef1234567890');

    // Delete a host
    $client->rbl->hosts->delete('HT1a2b3c4d5e6f7890abcdef1234567890');


    // ===== RBL Check =====

    // Start a manual check
    $checkResult = $client->rbl->check->start([
        'host' => '192.168.1.100',
        'callback' => 'https://myserver.com/callback',
        'details' => 1
    ]);

    $checkId = $checkResult['data']['id'];

    // Get check status
    $status = $client->rbl->check->status($checkId, ['details' => 1]);


    // ===== Listings =====

    // Get currently listed hosts
    $listings = $client->rbl->listings->get();


    // ===== Profiles =====

    // List profiles
    $profiles = $client->rbl->profiles->get();

    // Get a single profile
    $profile = $client->rbl->profiles->get('RP9f8e7d6c5b4a3210fedcba0987654321');

    // Create a profile
    $newProfile = $client->rbl->profiles->create([
        'name' => 'My Custom Profile',
        'rbls' => 'RB123...,RB456...'
    ]);

    // Update a profile
    $client->rbl->profiles->update('RP9f8e7d6c5b4a3210fedcba0987654321', [
        'name' => 'Updated Profile Name'
    ]);

    // Delete a profile
    $client->rbl->profiles->delete('RP9f8e7d6c5b4a3210fedcba0987654321');


    // ===== Sources =====

    // List sources
    $sources = $client->rbl->sources->get();

    // Get a single source
    $source = $client->rbl->sources->get('RB1234567890abcdef');

    // Create a source
    $newSource = $client->rbl->sources->create([
        'name' => 'My Custom RBL',
        'host' => 'custom.rbl.example.com'
    ]);

    // Update a source
    $client->rbl->sources->update('RB1234567890abcdef', [
        'name' => 'Updated RBL Name'
    ]);

    // Pause a source
    $client->rbl->sources->pause('RB1234567890abcdef');

    // Resume a source
    $client->rbl->sources->resume('RB1234567890abcdef');

    // Delete a source
    $client->rbl->sources->delete('RB1234567890abcdef');


    // ===== Contacts =====

    // List contacts
    $contacts = $client->contact->contacts->get();

    // Get a single contact
    $contact = $client->contact->contacts->get('COabcdef1234567890abcdef1234567890');

    // Create a contact
    $newContact = $client->contact->contacts->create([
        'email' => 'admin@example.com',
        'type' => 'email'
    ]);

    // Update a contact
    $client->contact->contacts->update('COabcdef1234567890abcdef1234567890', [
        'email' => 'updated@example.com'
    ]);

    // Pause a contact
    $client->contact->contacts->pause('COabcdef1234567890abcdef1234567890');

    // Resume a contact
    $client->contact->contacts->resume('COabcdef1234567890abcdef1234567890');

    // Confirm a contact
    $client->contact->contacts->confirm('COabcdef1234567890abcdef1234567890', [
        'authcode' => '123456'
    ]);

    // Resend confirmation
    $client->contact->contacts->resend('COabcdef1234567890abcdef1234567890');

    // Delete a contact
    $client->contact->contacts->delete('COabcdef1234567890abcdef1234567890');


    // ===== Contact Groups =====

    // List groups
    $groups = $client->contact->groups->get();

    // Get a single group
    $group = $client->contact->groups->get('CG4f3e2d1c0b9a8776655443322110fed');

    // Create a group
    $newGroup = $client->contact->groups->create([
        'name' => 'Primary Contacts',
        'contacts' => 'CT123...,CT456...'
    ]);

    // Update a group
    $client->contact->groups->update('CG4f3e2d1c0b9a8776655443322110fed', [
        'name' => 'Updated Group Name'
    ]);

    // Delete a group
    $client->contact->groups->delete('CG4f3e2d1c0b9a8776655443322110fed');

} catch (GeneratorLabs\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
