<?php declare(strict_types=1);

//
// This file is part of the Generator Labs PHP SDK package.
//
// (c) Generator Labs <support@generatorlabs.com>
//
// For the full copyright and license information, please view the LICENSE
// file that was distributed with this source code.
//

namespace GeneratorLabs\API;

use GeneratorLabs\Response;

/**
 * @phpstan-import-type RequestOptions from \GeneratorLabs\Client
 */
final class Account
{
    use RequestHandler;

    //
    // constructor to copy over the client reference
    //
    public function __construct(\GeneratorLabs\Client $_client)
    {
        $this->init($_client);
    }

    //
    // get the account: its balance, whether its checks are running, and plan usage
    //
    /** @param RequestOptions $_options */
    public function get(array $_options = []): Response
    {
        return $this->_get('account', null, $_options);
    }

    //
    // get the account balance
    //
    /** @param RequestOptions $_options */
    public function balance(array $_options = []): float
    {
        return (float) $this->_getPlain('account/balance', $_options);
    }

    //
    // get the account's usage by month (months)
    //
    /**
     * @param array<string, mixed>|null $_settings
     * @param RequestOptions $_options
     */
    public function usage(?array $_settings = null, array $_options = []): Response
    {
        return $this->_get('account/usage', $_settings, $_options);
    }

    //
    // pause the account's scheduled checks
    //
    /** @param RequestOptions $_options */
    public function pauseChecks(array $_options = []): Response
    {
        return $this->_post('account/checks/pause', null, $_options);
    }

    //
    // resume the account's scheduled checks
    //
    /** @param RequestOptions $_options */
    public function resumeChecks(array $_options = []): Response
    {
        return $this->_post('account/checks/resume', null, $_options);
    }
}
