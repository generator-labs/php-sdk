# Publishing the PHP SDK to Packagist

This guide explains how to publish the Generator Labs PHP SDK to Packagist.

## Prerequisites

1. **GitHub Repository** - Already set up at `github.com/generator-labs/php-sdk`
2. **Packagist Account** - Create an account at https://packagist.org
3. **Composer Package Name** - `generatorlabs/sdk` (defined in composer.json)
4. **Git Tags** - For version releases

## Step 1: Prepare the Package

Ensure `composer.json` is properly configured:

```json
{
    "name": "generatorlabs/sdk",
    "type": "library",
    "description": "A PHP wrapper for the Generator Labs API",
    "keywords": ["generatorlabs", "rbl", "dnsbl", "monitoring", "api"],
    "homepage": "https://generatorlabs.com",
    "license": "MIT",
    "support": {
        "issues": "https://github.com/generator-labs/php-sdk/issues",
        "source": "https://github.com/generator-labs/php-sdk"
    }
}
```

## Step 2: Create a GitHub Release

1. Update `CHANGELOG.md` with release notes
2. Commit all changes
3. Create a git tag:
   ```bash
   git tag -a v2.0.0 -m "Release v2.0.0"
   git push origin v2.0.0
   ```
4. Create a GitHub Release from the tag

## Step 3: Submit to Packagist

1. Log in to https://packagist.org
2. Click "Submit" in the top navigation
3. Enter the GitHub repository URL: `https://github.com/generator-labs/php-sdk`
4. Click "Check"
5. Click "Submit" to add the package

## Step 4: Set Up Auto-Update Hook

Packagist needs to be notified when you push changes:

### Option A: GitHub Service Hook (Recommended)

1. Go to your GitHub repository settings
2. Navigate to "Webhooks" → "Add webhook"
3. Set payload URL to: `https://packagist.org/api/github?username=YOUR_PACKAGIST_USERNAME`
4. Set content type to: `application/json`
5. Set secret to your Packagist API token (from https://packagist.org/profile/)
6. Select "Just the push event"
7. Save webhook

### Option B: Manual Update

From your Packagist package page, click "Update" to manually trigger updates.

## Step 5: Verify Installation

Test that users can install your package:

```bash
composer require generatorlabs/sdk
```

## Publishing Future Releases

For subsequent releases:

1. Update version in code (if applicable)
2. Update `CHANGELOG.md`
3. Commit changes
4. Create and push a new git tag:
   ```bash
   git tag -a v2.0.1 -m "Release v2.0.1"
   git push origin v2.0.1
   ```
5. Create GitHub Release
6. Packagist will auto-update via webhook

## Version Numbering

Follow [Semantic Versioning](https://semver.org/):

- **MAJOR** version (v3.0.0) - Incompatible API changes
- **MINOR** version (v2.1.0) - Backward-compatible functionality
- **PATCH** version (v2.0.1) - Backward-compatible bug fixes

## Best Practices

1. **Always tag releases** - Packagist relies on git tags
2. **Maintain CHANGELOG.md** - Document all changes
3. **Test before releasing** - Run full test suite
4. **Use semantic versioning** - Helps users understand impact
5. **Write clear commit messages** - They appear in Packagist
6. **Keep README updated** - It's shown on Packagist page

## Troubleshooting

### Package not updating on Packagist

- Check that GitHub webhook is configured
- Manually trigger update from Packagist
- Verify git tags are pushed to GitHub

### Composer can't find package

- Wait a few minutes after first submission
- Check package name matches exactly: `generatorlabs/sdk`
- Clear Composer cache: `composer clear-cache`

### Wrong version showing

- Ensure git tags follow format: `v2.0.0` or `2.0.0`
- Check that tags are pushed to GitHub
- Update package manually on Packagist

## Resources

- Packagist Documentation: https://packagist.org/about
- Composer Documentation: https://getcomposer.org/doc/
- Semantic Versioning: https://semver.org/
