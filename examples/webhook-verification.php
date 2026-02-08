<?php

/**
 * Example: Verifying webhook signatures
 *
 * This example shows how to verify incoming webhook requests from Generator Labs
 * using the SDK's built-in signature verification helper.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use GeneratorLabs\Webhook;
use GeneratorLabs\Exception;

/**
 * Your webhook's signing secret, available in the Edit Webhook panel of the Portal.
 * Store this securely (e.g., environment variable), never hard-code it.
 */
$signingSecret = getenv('GENERATOR_LABS_WEBHOOK_SECRET');

if (!$signingSecret) {
    die("Error: Set GENERATOR_LABS_WEBHOOK_SECRET environment variable\n");
}

/**
 * Read the signature header and raw body from the incoming request.
 * The raw body must be used (not a parsed version) for signature verification to work.
 */
$header = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] ?? '';
$body = file_get_contents('php://input');

/**
 * Example 1: Basic verification
 *
 * Verify the signature with the default 5-minute tolerance window.
 * On success, returns the decoded JSON payload as an associative array.
 * Throws an Exception if verification fails.
 */
try {
    $payload = Webhook::verify($body, $header, $signingSecret);

    echo "Webhook verified successfully!\n";
    echo "Event: " . ($payload['event'] ?? 'unknown') . "\n";

} catch (Exception $e) {
    http_response_code(403);
    echo "Verification failed: " . $e->getMessage() . "\n";
    exit(1);
}

/**
 * Example 2: Custom tolerance
 *
 * Set a custom tolerance window (in seconds) for timestamp validation.
 * Use 0 to disable timestamp checking entirely.
 */
try {
    // 10-minute tolerance
    $payload = Webhook::verify($body, $header, $signingSecret, 600);

} catch (Exception $e) {
    http_response_code(403);
    exit(1);
}

/**
 * Example 3: Usage in a typical webhook endpoint
 */
function handleWebhook(): void
{
    $secret = getenv('GENERATOR_LABS_WEBHOOK_SECRET');
    $header = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] ?? '';
    $body = file_get_contents('php://input');

    try {
        $payload = Webhook::verify($body, $header, $secret);
    } catch (Exception $e) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid signature']);
        return;
    }

    // Process the event
    switch ($payload['event'] ?? '') {
        case 'rbl.host.listed':
            // Handle host listed event
            break;
        case 'rbl.host.delisted':
            // Handle host delisted event
            break;
        case 'billing.balance.alert':
            // Handle low balance alert
            break;
        default:
            // Unknown event type
            break;
    }

    http_response_code(200);
    echo json_encode(['status' => 'ok']);
}
