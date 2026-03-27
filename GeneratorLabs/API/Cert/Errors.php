<?php declare(strict_types=1);

//
// This file is part of the Generator Labs PHP SDK package.
//
// (c) Generator Labs <support@generatorlabs.com>
//
// For the full copyright and license information, please view the LICENSE
// file that was distributed with this source code.
//

namespace GeneratorLabs\API\Cert;

use GeneratorLabs\API\RequestHandler;
use GeneratorLabs\Response;
use GeneratorLabs\API\PaginationTrait;

final class Errors
{
    use RequestHandler;
    use PaginationTrait;

    //
    // constructor to copy over the client reference
    //
    public function __construct(\GeneratorLabs\Client $_client)
    {
        $this->init($_client);
    }

    //
    // get certificate errors (read-only)
    //
    public function get(?array $_settings = null): Response
    {
        return $this->_get('cert/errors', $_settings);
    }

    //
    // override to extract errors from response
    //
    protected function getResourceName(): string
    {
        return 'errors';
    }
}
