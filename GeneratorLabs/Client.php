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

spl_autoload_register('GeneratorLabs\Client::autoload');

/**
 * Main API client for Generator Labs.
 *
 * The Generator Labs API is a RESTful web service API that lets customers manage
 * their RBL monitoring hosts, certificate monitoring, contacts, and retrieve
 * listing information.
 *
 * Example:
 * ```php
 * $client = new GeneratorLabs\Client('your_account_sid', 'your_auth_token');
 *
 * // RBL monitoring
 * $hosts = $client->rbl->hosts()->get();
 *
 * // Certificate monitoring
 * $monitors = $client->cert->monitors()->get();
 *
 * // Contact management
 * $contacts = $client->contact->contacts()->get();
 * ```
 *
 * Example with custom configuration:
 * ```php
 * $config = [
 *     'timeout' => 45.0,
 *     'max_retries' => 5,
 *     'retry_backoff' => 2,
 * ];
 * $client = new GeneratorLabs\Client('your_account_sid', 'your_auth_token', $config);
 * ```
 */
final class Client
{
    /**
     * PHP SDK version
     */
    public const VERSION = '2.0.1';

    /**
     * Account SID (2 uppercase + 32 lowercase hex characters)
     */
    private ?string $m_account_sid = null;

    /**
     * Authentication token (64 hex characters)
     */
    private ?string $m_api_token = null;

    /**
     * API base URL
     */
    private string $m_url = 'https://api.generatorlabs.com/4.0/';

    /**
     * Configuration options for timeouts and retries
     *
     * @var array{timeout: float, connect_timeout: float, max_retries: int, retry_backoff: int}
     */
    private array $m_config = [
        'timeout' => 30.0,
        'connect_timeout' => 5.0,
        'max_retries' => 3,
        'retry_backoff' => 1,
    ];

    /**
     * Additional CURL options (deprecated - use config instead)
     *
     * @deprecated Use $m_config array instead
     */
    public array $m_curl_opts = [];

    /**
     * RBL monitoring API namespace
     */
    private ?API\RBL $rbl = null;

    /**
     * Contact management API namespace
     */
    private ?API\Contact $contact = null;

    /**
     * Certificate monitoring API namespace
     */
    private ?API\Cert $cert = null;

    /**
     * Initialize the Generator Labs client.
     *
     * The account SID must be in the format of 2 uppercase letters followed by
     * 32 hexadecimal characters (e.g., "AC" + 32 hex chars).
     *
     * The auth token must be 64 hexadecimal characters.
     *
     * @param string $_account_sid Your Generator Labs account SID
     * @param string $_api_token Your Generator Labs auth token
     * @param array $_config Optional configuration array with keys:
     *                       - timeout: Request timeout in seconds (default: 30.0)
     *                       - connect_timeout: Connection timeout in seconds (default: 5.0)
     *                       - max_retries: Maximum retry attempts (default: 3)
     *                       - retry_backoff: Backoff multiplier for retries (default: 1)
     *                       - base_url: Custom API base URL
     * @throws Exception if account_sid or api_token format is invalid
     */
    public function __construct(string $_account_sid, string $_api_token, array $_config = [])
    {
        //
        // validate the credentials
        //
        if (preg_match('/^[A-Z]{2}[0-9a-f]{32}$/', $_account_sid) == 0)
        {
            throw new Exception('invalid API account sid provided.');
        }
        if (preg_match('/^[0-9a-f]{64}$/', $_api_token) == 0)
        {
            throw new Exception('invalid API access token provided.');
        }

        $this->account_sid($_account_sid);
        $this->api_token($_api_token);

        // Merge user config with defaults
        $this->m_config = array_merge($this->m_config, $_config);

        // Allow custom base URL
        if (isset($_config['base_url'])) {
            $this->url($_config['base_url']);
        }
    }

    /**
     * Autoloader for GeneratorLabs classes.
     *
     * Automatically loads GeneratorLabs classes when they are referenced.
     *
     * @param string $_name Fully qualified class name
     * @return void
     */
    static public function autoload(string $_name): void
    {
        if (strncmp($_name, 'GeneratorLabs', 13) == 0)
        {
            require_once str_replace('\\', '/', $_name) . '.php';
        }
    }

    /**
     * Get or set the account SID.
     *
     * @param string|null $_account_sid Account SID to set, or null to get current value
     * @return string|null Current account SID if getting, null if setting
     */
    public function account_sid(?string $_account_sid = null): mixed
    {
        if (is_null($_account_sid) == true)
        {
            return $this->m_account_sid;
        } else
        {
            $this->m_account_sid = $_account_sid;
        }

        return null;
    }

    /**
     * Get or set the API token.
     *
     * @param string|null $_api_token API token to set, or null to get current value
     * @return string|null Current API token if getting, null if setting
     */
    public function api_token(?string $_api_token = null): mixed
    {
        if (is_null($_api_token) == true)
        {
            return $this->m_api_token;
        } else
        {
            $this->m_api_token = $_api_token;
        }

        return null;
    }

    /**
     * Get or set the API base URL.
     *
     * @param string|null $_url Base URL to set, or null to get current value
     * @return string|null Current base URL if getting, null if setting
     */
    public function url(?string $_url = null): mixed
    {
        if (is_null($_url) == true)
        {
            return $this->m_url;
        } else
        {
            $this->m_url = $_url;
        }

        return null;
    }

    /**
     * Get configuration value(s).
     *
     * @param string|null $_key Configuration key to retrieve, or null to get all config
     * @return mixed Configuration value if key provided, entire config array if null
     */
    public function config(?string $_key = null): mixed
    {
        if (is_null($_key)) {
            return $this->m_config;
        }
        return $this->m_config[$_key] ?? null;
    }

    /**
     * Set additional CURL options.
     *
     * @deprecated Use config array in constructor instead
     * @param array|null $_opts CURL options array
     * @return void
     */
    public function curl_opts(?array $_opts = null): void
    {
        $this->m_curl_opts = $_opts;
    }

    /**
     * Magic getter for API resource namespaces.
     *
     * Provides lazy-loaded access to:
     * - $client->rbl: RBL monitoring operations (hosts, profiles, sources, etc.)
     * - $client->contact: Contact management operations (contacts, groups)
     * - $client->cert: Certificate monitoring operations (monitors, profiles, errors)
     *
     * Example:
     * ```php
     * $hosts = $client->rbl->hosts()->get();
     * $monitors = $client->cert->monitors()->get();
     * $contacts = $client->contact->contacts()->get();
     * ```
     *
     * @param string $_name Resource name ('rbl', 'contact', or 'cert')
     * @return API\RBL|API\Contact|API\Cert Resource namespace instance
     * @throws Exception if resource name is invalid
     */
    public function __get(string $_name): mixed
    {
        return match ($_name) {
            'rbl' => $this->rbl ?? ($this->rbl = new API\RBL($this)),
            'contact' => $this->contact ?? ($this->contact = new API\Contact($this)),
            'cert' => $this->cert ?? ($this->cert = new API\Cert($this)),
            default => throw new Exception('invalid resource ' . $_name),
        };
    }
}
