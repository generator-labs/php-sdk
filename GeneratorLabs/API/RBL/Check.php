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
    public function start(array $_data): array
    {
        return $this->_post('rbl/check/start', $_data);
    }

    //
    // get the status of an RBL check
    //
    public function status(string $_id): array
    {
        return $this->_get('rbl/check/status/' . $_id);
    }
}
