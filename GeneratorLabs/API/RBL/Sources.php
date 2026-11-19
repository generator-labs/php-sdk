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
final class Sources
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
    // get a list of sources or a single source by id
    //
    /**
     * @param string|array<string, mixed>|null $_id_or_settings
     * @param RequestOptions $_options
     */
    public function get(string|array|null $_id_or_settings = null, array $_options = []): Response
    {
        if (is_string($_id_or_settings)) {
            return $this->_get('rbl/sources/' . $_id_or_settings, null, $_options);
        }
        return $this->_get('rbl/sources', $_id_or_settings, $_options);
    }

    //
    // create a new source
    //
    /**
     * @param array<string, mixed> $_data
     * @param RequestOptions $_options
     */
    public function create(array $_data, array $_options = []): Response
    {
        return $this->_post('rbl/sources', $_data, $_options, true);
    }

    //
    // update a source
    //
    /**
     * @param array<string, mixed> $_data
     * @param RequestOptions $_options
     */
    public function update(string $_id, array $_data, array $_options = []): Response
    {
        return $this->_put('rbl/sources/' . $_id, $_data, $_options);
    }

    //
    // delete a source by id
    //
    /** @param RequestOptions $_options */
    public function delete(string $_id, array $_options = []): Response
    {
        return $this->_delete('rbl/sources/' . $_id, null, $_options);
    }

    //
    // pause a source by id
    //
    /** @param RequestOptions $_options */
    public function pause(string $_id, array $_options = []): Response
    {
        return $this->_post('rbl/sources/' . $_id . '/pause', null, $_options);
    }

    //
    // resume (un-pause) a source by id
    //
    /** @param RequestOptions $_options */
    public function resume(string $_id, array $_options = []): Response
    {
        return $this->_post('rbl/sources/' . $_id . '/resume', null, $_options);
    }

    //
    // get the resource name for pagination
    //
    protected function getResourceName(): string
    {
        return 'sources';
    }
}
