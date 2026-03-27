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
    public function get(string|array|null $_id_or_settings = null): Response
    {
        if (is_string($_id_or_settings)) {
            return $this->_get('rbl/sources/' . $_id_or_settings);
        }
        return $this->_get('rbl/sources', $_id_or_settings);
    }

    //
    // create a new source
    //
    public function create(array $_data): Response
    {
        return $this->_post('rbl/sources', $_data);
    }

    //
    // update a source
    //
    public function update(string $_id, array $_data): Response
    {
        return $this->_put('rbl/sources/' . $_id, $_data);
    }

    //
    // delete a source by id
    //
    public function delete(string $_id): Response
    {
        return $this->_delete('rbl/sources/' . $_id);
    }

    //
    // pause a source by id
    //
    public function pause(string $_id): Response
    {
        return $this->_post('rbl/sources/' . $_id . '/pause');
    }

    //
    // resume (un-pause) a source by id
    //
    public function resume(string $_id): Response
    {
        return $this->_post('rbl/sources/' . $_id . '/resume');
    }

    //
    // get the resource name for pagination
    //
    protected function getResourceName(): string
    {
        return 'sources';
    }
}
