# Generator Labs PHP SDK

[![Tests](https://github.com/generator-labs/php-sdk/actions/workflows/tests.yml/badge.svg)](https://github.com/generator-labs/php-sdk/actions/workflows/tests.yml)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%208-brightgreen.svg)](https://phpstan.org/)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-8892BF.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

The official PHP SDK for the [Generator Labs](https://generatorlabs.com) API v4.0.

## Features

- Full support for Generator Labs API v4.0
- RESTful endpoint design with proper HTTP verbs (GET, POST, PUT, DELETE)
- RBL and DNSBL monitoring
- Contact and contact group management
- Manual RBL checks
- Monitoring profiles and sources
- Type-safe with PHP 8.0+ strict types
- PSR-4 autoloading

## Prerequisites

Before using this library, you must have:

* A Generator Labs account - [Sign up](https://portal.generatorlabs.com/signup/) or [Login](https://portal.generatorlabs.com/login/)
* Valid API credentials (Account SID and Auth Token) from the [Portal](https://portal.generatorlabs.com/login/)
* PHP >= 8.1
* The PHP cURL extension
* The PHP JSON extension

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

$client = new GeneratorLabs\Client('your_account_sid', 'your_auth_token');
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
    $host = $client->rbl->hosts->get('HTee06c4fa7c23aa8a3a4e8d66922b0834');

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
        'rbl_profile' => 'RP15d4e891d784977cacbfcbb00c48f133',
        'contact_group' => 'CG37106c6baa1ec90a2b3f5c8ec54afe9d'
    ]);

    print_r($result);

} catch(GeneratorLabs\Exception $e) {
    echo $e->getMessage();
}
```

### Update a Host

```php
try {
    $result = $client->rbl->hosts->update('HTee06c4fa7c23aa8a3a4e8d66922b0834', [
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
    $result = $client->rbl->hosts->delete('HTee06c4fa7c23aa8a3a4e8d66922b0834');

    print_r($result);

} catch(GeneratorLabs\Exception $e) {
    echo $e->getMessage();
}
```

### Pause/Resume a Host

```php
try {
    // Pause monitoring
    $client->rbl->hosts->pause('HTee06c4fa7c23aa8a3a4e8d66922b0834');

    // Resume monitoring
    $client->rbl->hosts->resume('HTee06c4fa7c23aa8a3a4e8d66922b0834');

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
    $client->contact->contacts->update('CT1234567890abcdef', [
        'email' => 'updated@example.com'
    ]);

    // Confirm a contact
    $client->contact->contacts->confirm('CT1234567890abcdef', [
        'authcode' => '123456'
    ]);

    // Delete a contact
    $client->contact->contacts->delete('CT1234567890abcdef');

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
    $client->contact->groups->update('CG1234567890abcdef', [
        'name' => 'Updated Group Name'
    ]);

    // Delete a contact group
    $client->contact->groups->delete('CG1234567890abcdef');

} catch(GeneratorLabs\Exception $e) {
    echo $e->getMessage();
}
```

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
