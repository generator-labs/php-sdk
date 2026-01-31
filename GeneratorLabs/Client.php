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

//
// register the autoloader
//
spl_autoload_register('GeneratorLabs\Client::autoload');

final class Client
{
    //
    // PHP SDK version
    //
    public const VERSION = '2.0.0';

    //
    // the API credentials
    //
    private ?string $m_account_sid = null;
    private ?string $m_api_token = null;

    //
    // the request URL
    //
    private string $m_url = 'https://api.generatorlabs.com/4.0/';

    //
    // additional CURL opts
    //
    public array $m_curl_opts = [];

    //
    // resource containers for v4.0 API structure
    //
    private ?API\RBL $rbl = null;
    private ?API\Contact $contact = null;

    //
    // init the object and set the API credentials
    //
    public function __construct(string $_account_sid, string $_api_token)
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
    }

    //
    // autoloader
    //
    static public function autoload(string $_name): void
    {
        if (strncmp($_name, 'GeneratorLabs', 13) == 0)
        {
            require_once str_replace('\\', '/', $_name) . '.php';
        }
    }

    //
    // get/set internal values
    //
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

    //
    // here to support adding additional custom curl opts
    //
    public function curl_opts(?array $_opts = null): void
    {
        $this->m_curl_opts = $_opts;
    }

    //
    // magic getter for resource containers (v4.0 API structure)
    //
    public function __get(string $_name): mixed
    {
        return match ($_name) {
            'rbl' => $this->rbl ?? ($this->rbl = new API\RBL($this)),
            'contact' => $this->contact ?? ($this->contact = new API\Contact($this)),
            default => throw new Exception('invalid resource ' . $_name),
        };
    }
}
