<?php declare(strict_types=1);

require 'vendor/autoload.php';

// Initialize client
$client = new GeneratorLabs\Client('AC12345678901234567890123456789012', '0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef');

try {
    // ===== RBL Hosts =====

    // List all hosts
    $hosts = $client->rbl->hosts->get(['page_size' => 10]);

    // Get a single host
    $host = $client->rbl->hosts->get('HTee06c4fa7c23aa8a3a4e8d66922b0834');

    // Create a host
    $newHost = $client->rbl->hosts->create([
        'name' => 'My Mail Server',
        'host' => '192.168.1.100',
        'type' => 'rbl',
        'rbl_profile' => 'RP15d4e891d784977cacbfcbb00c48f133',
        'contact_group' => 'CG37106c6baa1ec90a2b3f5c8ec54afe9d'
    ]);

    // Update a host
    $client->rbl->hosts->update('HTee06c4fa7c23aa8a3a4e8d66922b0834', [
        'name' => 'Updated Server Name'
    ]);

    // Pause a host
    $client->rbl->hosts->pause('HTee06c4fa7c23aa8a3a4e8d66922b0834');

    // Resume a host
    $client->rbl->hosts->resume('HTee06c4fa7c23aa8a3a4e8d66922b0834');

    // Delete a host
    $client->rbl->hosts->delete('HTee06c4fa7c23aa8a3a4e8d66922b0834');


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
    $profile = $client->rbl->profiles->get('RP15d4e891d784977cacbfcbb00c48f133');

    // Create a profile
    $newProfile = $client->rbl->profiles->create([
        'name' => 'My Custom Profile',
        'rbls' => 'RB123...,RB456...'
    ]);

    // Update a profile
    $client->rbl->profiles->update('RP15d4e891d784977cacbfcbb00c48f133', [
        'name' => 'Updated Profile Name'
    ]);

    // Delete a profile
    $client->rbl->profiles->delete('RP15d4e891d784977cacbfcbb00c48f133');


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
    $contact = $client->contact->contacts->get('CT1234567890abcdef');

    // Create a contact
    $newContact = $client->contact->contacts->create([
        'email' => 'admin@example.com',
        'type' => 'email'
    ]);

    // Update a contact
    $client->contact->contacts->update('CT1234567890abcdef', [
        'email' => 'updated@example.com'
    ]);

    // Pause a contact
    $client->contact->contacts->pause('CT1234567890abcdef');

    // Resume a contact
    $client->contact->contacts->resume('CT1234567890abcdef');

    // Confirm a contact
    $client->contact->contacts->confirm('CT1234567890abcdef', [
        'authcode' => '123456'
    ]);

    // Resend confirmation
    $client->contact->contacts->resend('CT1234567890abcdef');

    // Delete a contact
    $client->contact->contacts->delete('CT1234567890abcdef');


    // ===== Contact Groups =====

    // List groups
    $groups = $client->contact->groups->get();

    // Get a single group
    $group = $client->contact->groups->get('CG37106c6baa1ec90a2b3f5c8ec54afe9d');

    // Create a group
    $newGroup = $client->contact->groups->create([
        'name' => 'Primary Contacts',
        'contacts' => 'CT123...,CT456...'
    ]);

    // Update a group
    $client->contact->groups->update('CG37106c6baa1ec90a2b3f5c8ec54afe9d', [
        'name' => 'Updated Group Name'
    ]);

    // Delete a group
    $client->contact->groups->delete('CG37106c6baa1ec90a2b3f5c8ec54afe9d');

} catch (GeneratorLabs\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
