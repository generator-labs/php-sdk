# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Pagination support for list endpoints
- Configuration options for timeout and retry settings
- Code coverage reporting
- Security policy documentation

## [2.0.0] - 2026-01-31

### Added
- Initial release with v4.0 API support
- Guzzle HTTP client with retry logic and exponential backoff
- Automatic retries on connection errors, 5xx errors, and 429 rate limits
- Configurable timeouts (5s connection, 30s request)
- RBL monitoring endpoints (hosts, profiles, sources, check, listings)
- Contact management endpoints (contacts, groups)
- Credential validation (account SID and auth token format)
- PHPStan level 8 static analysis
- Comprehensive test suite
- PHP 8.1+ support

### Changed
- Replaced cURL with Guzzle HTTP client
- Switched to plural-only endpoint naming convention
- Simplified exception handling with single Exception class

### Security
- Added User-Agent header for API analytics
- Implemented secure credential validation
