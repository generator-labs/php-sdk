<?php declare(strict_types=1);

//
// This file is part of the Generator Labs PHP SDK package.
//
// (c) Generator Labs <support@generatorlabs.com>
//
// For the full copyright and license information, please view the LICENSE
// file that was distributed with this source code.
//

namespace GeneratorLabs;

use GeneratorLabs\Exception;

/**
 * Webhook signature verification utility.
 *
 * Verifies that incoming webhook requests were sent by Generator Labs
 * using HMAC-SHA256 signatures.
 *
 * Example:
 * ```php
 * $header = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] ?? '';
 * $body = file_get_contents('php://input');
 *
 * $event = GeneratorLabs\Webhook::verify($body, $header, $signing_secret);
 * ```
 */
final class Webhook
{
    /**
     * Default tolerance in seconds for timestamp validation (5 minutes).
     */
    public const DEFAULT_TOLERANCE = 300;

    /**
     * Verify a webhook signature and return the decoded payload.
     *
     * @param string $_body The raw request body
     * @param string $_header The X-Webhook-Signature header value
     * @param string $_secret Your webhook's signing secret
     * @param int $_tolerance Maximum age of the request in seconds (0 to disable)
     * @return array The decoded JSON payload
     * @throws Exception if verification fails
     */
    public static function verify(string $_body, string $_header, string $_secret, int $_tolerance = self::DEFAULT_TOLERANCE): array
    {
        if (strlen($_header) == 0)
        {
            throw new Exception('missing X-Webhook-Signature header.');
        }

        //
        // parse the header: t=timestamp,v1=signature
        //
        $parts = [];
        foreach (explode(',', $_header) as $part)
        {
            $pair = explode('=', $part, 2);
            if (count($pair) == 2)
            {
                $parts[$pair[0]] = $pair[1];
            }
        }

        if (!isset($parts['t']) || !isset($parts['v1']))
        {
            throw new Exception('invalid X-Webhook-Signature header format.');
        }

        //
        // check timestamp tolerance
        //
        if ($_tolerance > 0 && abs(time() - intval($parts['t'])) > $_tolerance)
        {
            throw new Exception('webhook timestamp is outside the tolerance window.');
        }

        //
        // compute and compare the signature
        //
        $expected = hash_hmac('sha256', $parts['t'] . '.' . $_body, $_secret);

        if (!hash_equals($expected, $parts['v1']))
        {
            throw new Exception('webhook signature verification failed.');
        }

        //
        // decode and return the payload
        //
        $payload = json_decode($_body, true);
        if (is_null($payload))
        {
            throw new Exception('failed to decode webhook JSON payload.');
        }

        return $payload;
    }
}
