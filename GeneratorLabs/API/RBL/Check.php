<?php declare(strict_types=1);

//
// This file is part of the Generator Labs PHP SDK package.
//
// (c) Generator Labs <support@generatorlabs.com>
//
// For the full copyright and license information, please view the LICENSE
// file that was distributed with this source code.
//

namespace GeneratorLabs\API\RBL;

use GeneratorLabs\API\RequestHandler;
use GeneratorLabs\Response;

/**
 * @phpstan-import-type RequestOptions from \GeneratorLabs\Client
 */
final class Check
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
    // start a new RBL check
    //
    /**
     * @param array<string, mixed> $_data
     * @param RequestOptions $_options
     */
    public function start(array $_data, array $_options = []): Response
    {
        return $this->_post('rbl/check/start', $_data, $_options, true);
    }

    //
    // get the status of an RBL check
    //
    /**
     * @param array<string, mixed>|null $_params
     * @param RequestOptions $_options
     */
    public function status(string $_id, ?array $_params = null, array $_options = []): Response
    {
        return $this->_get('rbl/check/status/' . $_id, $_params, $_options);
    }
}
