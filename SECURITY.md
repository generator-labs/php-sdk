# Security Policy

## Supported Versions

We release patches for security vulnerabilities for the following versions:

| Version | Supported          |
| ------- | ------------------ |
| 2.0.x   | :white_check_mark: |
| < 2.0   | :x:                |

## Reporting a Vulnerability

If you discover a security vulnerability within the Generator Labs PHP SDK, please send an email to security@generatorlabs.com. All security vulnerabilities will be promptly addressed.

**Please do not report security vulnerabilities through public GitHub issues.**

### What to Include

When reporting a vulnerability, please include:

- Description of the vulnerability
- Steps to reproduce the issue
- Potential impact
- Suggested fix (if any)

### Response Timeline

- **Initial Response**: Within 48 hours
- **Status Update**: Within 5 business days
- **Fix Timeline**: Varies by severity (Critical: < 7 days, High: < 14 days, Medium: < 30 days)

## Security Best Practices

When using this SDK:

1. **Never commit credentials** - Use environment variables or secure configuration management
2. **Keep dependencies updated** - Regularly update to the latest version
3. **Use HTTPS only** - The SDK enforces HTTPS for API calls
4. **Validate input** - Sanitize user input before passing to API methods
5. **Monitor rate limits** - Implement proper error handling for 429 responses
6. **Secure credential storage** - Use secure vaults or environment variables for API credentials

## Security Scanning

This SDK uses:
- **PHPStan Level 8** - Maximum static analysis for type safety and code quality
- **Dependabot** - Automated dependency updates and vulnerability alerts
- **Composer Audit** - Regular security audits of dependencies

Run security checks locally:
```bash
composer audit              # Check for known vulnerabilities
composer run-script phpstan # Run static analysis
```

## Known Security Considerations

- API credentials (account SID and auth token) are transmitted using HTTP Basic Authentication over HTTPS
- The SDK validates credential format but does not validate credential strength
- Retry logic includes exponential backoff to prevent accidental DoS
