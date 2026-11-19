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
final class Hosts
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
    // get a list of hosts, or a single host by id
    //
    /**
     * @param string|array<string, mixed>|null $_id_or_settings
     * @param RequestOptions $_options
     */
    public function get(string|array|null $_id_or_settings = null, array $_options = []): Response
    {
        // If string, it's an ID for a single host
        if (is_string($_id_or_settings))
        {
            return $this->_get('rbl/hosts/' . $_id_or_settings, null, $_options);
        }

        // Otherwise, it's query parameters for listing
        return $this->_get('rbl/hosts', $_id_or_settings, $_options);
    }

    //
    // create a new host
    //
    /**
     * @param array<string, mixed> $_data
     * @param RequestOptions $_options
     */
    public function create(array $_data, array $_options = []): Response
    {
        return $this->_post('rbl/hosts', $_data, $_options, true);
    }

    //
    // update a host
    //
    /**
     * @param array<string, mixed> $_data
     * @param RequestOptions $_options
     */
    public function update(string $_id, array $_data, array $_options = []): Response
    {
        return $this->_put('rbl/hosts/' . $_id, $_data, $_options);
    }

    //
    // delete a host by id
    //
    /** @param RequestOptions $_options */
    public function delete(string $_id, array $_options = []): Response
    {
        return $this->_delete('rbl/hosts/' . $_id, null, $_options);
    }

    //
    // pause a host by id
    //
    /** @param RequestOptions $_options */
    public function pause(string $_id, array $_options = []): Response
    {
        return $this->_post('rbl/hosts/' . $_id . '/pause', null, $_options);
    }

    //
    // resume (un-pause) a host by id
    //
    /** @param RequestOptions $_options */
    public function resume(string $_id, array $_options = []): Response
    {
        return $this->_post('rbl/hosts/' . $_id . '/resume', null, $_options);
    }

    //
    // override to extract hosts from response
    //
    protected function getResourceName(): string
    {
        return 'hosts';
    }
}
