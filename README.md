# Generator Labs PHP SDK

[![Tests](https://github.com/generator-labs/php-sdk/actions/workflows/tests.yml/badge.svg)](https://github.com/generator-labs/php-sdk/actions/workflows/tests.yml)
[![codecov](https://codecov.io/gh/generator-labs/php-sdk/branch/master/graph/badge.svg)](https://codecov.io/gh/generator-labs/php-sdk)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%208-brightgreen.svg)](https://phpstan.org/)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-8892BF.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

The official PHP SDK for the [Generator Labs](https://generatorlabs.com) API v4.0.

## Features

- Full support for Generator Labs API v4.0
- Automatic retry logic with exponential backoff (configurable)
- Configurable timeouts and retry behavior
- Automatic pagination support for large result sets
- RESTful endpoint design with proper HTTP verbs (GET, POST, PUT, DELETE)
- RBL and DNSBL monitoring
- Contact and contact group management
- Manual RBL checks
- Monitoring profiles and sources
- Type-safe with PHP 8.1+ strict types
- PHPStan level 8 static analysis
- Guzzle HTTP client with connection pooling
- PSR-4 autoloading

## Prerequisites

Before using this library, you must have:

* A Generator Labs account - [Sign up](https://portal.generatorlabs.com/signup/) or [Login](https://portal.generatorlabs.com/login/)
* Valid API credentials (Account SID and Auth Token) from the [Portal](https://portal.generatorlabs.com/login/)
* PHP >= 8.1
* The PHP JSON extension
* Guzzle HTTP client (automatically installed via Composer)

## Installation

Install via Composer:

```bash
composer require generatorlabs/sdk
```

## Quick Start

### Initialize the Client

```php
<?php

require 'vendor/autoload.php';

// Basic initialization
$client = new GeneratorLabs\Client('your_account_sid', 'your_auth_token');

// With custom configuration
$client = new GeneratorLabs\Client(
    'your_account_sid',
    'your_auth_token',
    [
        'timeout' => 45,          // Request timeout in seconds
        'connect_timeout' => 10,  // Connection timeout in seconds
        'max_retries' => 5,       // Maximum retry attempts
        'retry_backoff' => 2      // Backoff multiplier (2x: 1s, 2s, 4s, 8s, 16s)
    ]
);
```

### List Hosts

```php
try {
    $hosts = $client->rbl->hosts->get(['page_size' => 10, 'page' => 1]);

    print_r($hosts);

} catch(GeneratorLabs\Exception $e) {
    echo $e->getMessage();
}
```

### Get a Single Host

```php
try {
    $host = $client->rbl->hosts->get('HT1a2b3c4d5e6f7890abcdef1234567890');

    print_r($host);

} catch(GeneratorLabs\Exception $e) {
    echo $e->getMessage();
}
```

### Create a New Host

```php
try {
    $result = $client->rbl->hosts->create([
        'name' => 'My Mail Server',
        'host' => '192.168.1.100',
        'type' => 'rbl',
        'rbl_profile' => 'RP9f8e7d6c5b4a3210fedcba0987654321',
        'contact_group' => 'CG4f3e2d1c0b9a8776655443322110fed'
    ]);

    print_r($result);

} catch(GeneratorLabs\Exception $e) {
    echo $e->getMessage();
}
```

### Update a Host

```php
try {
    $result = $client->rbl->hosts->update('HT1a2b3c4d5e6f7890abcdef1234567890', [
        'name' => 'Updated Mail Server Name'
    ]);

    print_r($result);

} catch(GeneratorLabs\Exception $e) {
    echo $e->getMessage();
}
```

### Delete a Host

```php
try {
    $result = $client->rbl->hosts->delete('HT1a2b3c4d5e6f7890abcdef1234567890');

    print_r($result);

} catch(GeneratorLabs\Exception $e) {
    echo $e->getMessage();
}
```

### Pause/Resume a Host

```php
try {
    // Pause monitoring
    $client->rbl->hosts->pause('HT1a2b3c4d5e6f7890abcdef1234567890');

    // Resume monitoring
    $client->rbl->hosts->resume('HT1a2b3c4d5e6f7890abcdef1234567890');

} catch(GeneratorLabs\Exception $e) {
    echo $e->getMessage();
}
```

### Start a Manual RBL Check

```php
try {
    $result = $client->rbl->check->start([
        'host' => '192.168.1.100',
        'callback' => 'https://myserver.com/callback',
        'details' => 1
    ]);

    $checkId = $result['data']['id'];

    // Get check status
    $status = $client->rbl->check->status($checkId, ['details' => 1]);

    print_r($status);

} catch(GeneratorLabs\Exception $e) {
    echo $e->getMessage();
}
```

### Manage Contacts

```php
try {
    // List contacts
    $contacts = $client->contact->contacts->get();

    // Create a contact
    $result = $client->contact->contacts->create([
        'email' => 'admin@example.com',
        'type' => 'email'
    ]);

    // Update a contact
    $client->contact->contacts->update('COabcdef1234567890abcdef1234567890', [
        'email' => 'updated@example.com'
    ]);

    // Confirm a contact
    $client->contact->contacts->confirm('COabcdef1234567890abcdef1234567890', [
        'authcode' => '123456'
    ]);

    // Delete a contact
    $client->contact->contacts->delete('COabcdef1234567890abcdef1234567890');

} catch(GeneratorLabs\Exception $e) {
    echo $e->getMessage();
}
```

### Manage Contact Groups

```php
try {
    // List contact groups
    $groups = $client->contact->groups->get();

    // Create a contact group
    $result = $client->contact->groups->create([
        'name' => 'Primary Contacts',
        'contacts' => 'CT123...,CT456...'
    ]);

    // Update a contact group
    $client->contact->groups->update('CG4f3e2d1c0b9a8776655443322110fed', [
        'name' => 'Updated Group Name'
    ]);

    // Delete a contact group
    $client->contact->groups->delete('CG4f3e2d1c0b9a8776655443322110fed');

} catch(GeneratorLabs\Exception $e) {
    echo $e->getMessage();
}
```

### Certificate Monitoring

Certificate monitoring allows you to monitor SSL/TLS certificates for expiration, validity, and configuration issues across HTTPS, SMTPS, IMAPS, and other TLS-enabled services.

#### List Certificate Errors

```php
try {
    // List all certificate errors
    $errors = $client->cert->errors->get();

    // Get a specific error by ID
    $error = $client->cert->errors->get('CE5e6f7a8b9c0d1e2f3a4b5c6d7e8f9a');

    print_r($errors);

} catch(GeneratorLabs\Exception $e) {
    echo $e->getMessage();
}
```

#### Manage Certificate Monitors

```php
try {
    // List all certificate monitors
    $monitors = $client->cert->monitors->get();

    // Get a specific monitor
    $monitor = $client->cert->monitors->get('CM62944aeeee2b46d7a28221164f38976a');

    // Create a new certificate monitor
    $monitor = $client->cert->monitors->create([
        'name' => 'Production Web Server',
        'hostname' => 'example.com',
        'port' => 443,
        'protocol' => 'https',
        'cert_profile' => 'CP79b597e61a984a35b5eb7dcdbc3de53c',
        'contact_group' => 'CG4f3e2d1c0b9a8776655443322110fed'
    ]);

    // Update a monitor
    $monitor = $client->cert->monitors->update('CM62944aeeee2b46d7a28221164f38976a', [
        'name' => 'Updated Server Name'
    ]);

    // Delete a monitor
    $client->cert->monitors->delete('CM62944aeeee2b46d7a28221164f38976a');

    // Pause monitoring
    $client->cert->monitors->pause('CM62944aeeee2b46d7a28221164f38976a');

    // Resume monitoring
    $client->cert->monitors->resume('CM62944aeeee2b46d7a28221164f38976a');

} catch(GeneratorLabs\Exception $e) {
    echo $e->getMessage();
}
```

#### Manage Certificate Profiles

```php
try {
    // List all certificate profiles
    $profiles = $client->cert->profiles->get();

    // Get a specific profile
    $profile = $client->cert->profiles->get('CP79b597e61a984a35b5eb7dcdbc3de53c');

    // Create a new profile
    $profile = $client->cert->profiles->create([
        'name' => 'Standard Certificate Profile',
        'expiration_warning_days' => 30,
        'expiration_critical_days' => 7
    ]);

    // Update a profile
    $profile = $client->cert->profiles->update('CP79b597e61a984a35b5eb7dcdbc3de53c', [
        'expiration_warning_days' => 45
    ]);

    // Delete a profile
    $client->cert->profiles->delete('CP79b597e61a984a35b5eb7dcdbc3de53c');

} catch(GeneratorLabs\Exception $e) {
    echo $e->getMessage();
}
```

## Webhook Verification

The SDK includes a helper for verifying incoming webhook signatures. Each webhook is assigned a signing secret (available in the Portal), which is used to compute an HMAC-SHA256 signature sent with every request in the `X-Webhook-Signature` header.

```php
use GeneratorLabs\Webhook;
use GeneratorLabs\Exception;

$header = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] ?? '';
$body = file_get_contents('php://input');
$secret = getenv('GENERATOR_LABS_WEBHOOK_SECRET');

try {
    $payload = Webhook::verify($body, $header, $secret);

    // $payload is the decoded event data
    echo $payload['event'];

} catch (Exception $e) {
    // Signature verification failed
    http_response_code(403);
}
```

The default timestamp tolerance is 5 minutes. You can customize it (in seconds), or pass `0` to disable:

```php
$payload = Webhook::verify($body, $header, $secret, 600);  // 10-minute tolerance
$payload = Webhook::verify($body, $header, $secret, 0);    // disable timestamp check
```

See `examples/webhook-verification.php` for a complete example.

## API Documentation

Full API documentation is available at the [Generator Labs Developer Site](https://docs.generatorlabs.com/api/v4/).

## API Structure

The v4.0 API follows a RESTful design with two main resource namespaces:

### RBL Namespace (`$client->rbl`)

- **hosts** - List, get, create, update, delete, pause, and resume hosts
- **listings** - Get currently listed hosts
- **check** - Start manual checks and get status
- **profiles** - List, get, create, update, and delete monitoring profiles
- **sources** - List, get, create, update, delete, pause, and resume RBL sources

### Contact Namespace (`$client->contact`)

- **contacts** - List, get, create, update, delete, pause, resume, confirm, and resend contacts
- **groups** - List, get, create, update, and delete contact groups

### Certificate Namespace (`$client->cert`)

- **errors** - List certificate errors and get specific error details
- **monitors** - List, get, create, update, delete, pause, and resume certificate monitors
- **profiles** - List, get, create, update, and delete certificate monitoring profiles

## Development

### Running Tests

```bash
composer test
```

### Running Static Analysis

```bash
composer phpstan
```

### Running All Quality Checks

```bash
composer test && composer phpstan
```

## Release History

### v2.0.0 (2026-01-31)
* Complete rewrite for Generator Labs API v4.0
* RESTful endpoint design with proper HTTP verbs
* Updated to use Generator Labs branding (formerly RBLTracker)
* Minimum PHP version bumped to 8.1
* Added full PHPUnit test coverage
* Added PHPStan static analysis
* Added GitHub Actions CI/CD workflow
* Organized endpoints under `/rbl/` and `/contact/` namespaces
* Added support for PUT and DELETE methods
* Improved error handling for v4.0 response format

### v1.1.0
* Updated to use the new API endpoint URL
* Added strict type requirements; bumped minimum version to PHP 7.4
* Added support for Monitoring Profiles
* Added support for the ACLs endpoint

### v1.0.3
* Fixed typo in exception class
* Changed array() to []
* Updated classes to use final keyword

### v1.0.2
* Updated to support API v3.6
* Added manual RBL check support
* Refactored code layout

### v1.0.1
* Updated to support API v3.4

### v1.0.0
* Initial release

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Support

For questions, issues, or feature requests:

- GitHub Issues: https://github.com/generator-labs/php-sdk/issues
- Email: support@generatorlabs.com
- Documentation: https://docs.generatorlabs.com

## License

This library is released under the MIT License. See [LICENSE](LICENSE) for details.

## Links

- [Generator Labs Website](https://generatorlabs.com/)
- [API Documentation](https://docs.generatorlabs.com/api/v4/)
- [Sign Up](https://portal.generatorlabs.com/signup/)
- [Portal Login](https://portal.generatorlabs.com/login/)
