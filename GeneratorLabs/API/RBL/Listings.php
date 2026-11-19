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
use GeneratorLabs\API\PaginationTrait;

/**
 * @phpstan-import-type RequestOptions from \GeneratorLabs\Client
 */
final class Listings
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
    // get a list of RBL listings
    //
    /**
     * @param array<string, mixed>|null $_settings
     * @param RequestOptions $_options
     */
    public function get(?array $_settings = null, array $_options = []): Response
    {
        return $this->_get('rbl/listings', $_settings, $_options);
    }

    //
    // get the resource name for pagination
    //
    protected function getResourceName(): string
    {
        return 'listings';
    }
}
