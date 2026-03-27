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

final class Monitors
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
    // get a list of monitors, or a single monitor by id
    //
    public function get(string|array|null $_id_or_settings = null): Response
    {
        // If string, it's an ID for a single monitor
        if (is_string($_id_or_settings))
        {
            return $this->_get('cert/monitors/' . $_id_or_settings);
        }

        // Otherwise, it's query parameters for listing
        return $this->_get('cert/monitors', $_id_or_settings);
    }

    //
    // create a new monitor
    //
    public function create(array $_data): Response
    {
        return $this->_post('cert/monitors', $_data);
    }

    //
    // update a monitor
    //
    public function update(string $_id, array $_data): Response
    {
        return $this->_put('cert/monitors/' . $_id, $_data);
    }

    //
    // delete a monitor by id
    //
    public function delete(string $_id): Response
    {
        return $this->_delete('cert/monitors/' . $_id);
    }

    //
    // pause a monitor by id
    //
    public function pause(string $_id): Response
    {
        return $this->_post('cert/monitors/' . $_id . '/pause');
    }

    //
    // resume (un-pause) a monitor by id
    //
    public function resume(string $_id): Response
    {
        return $this->_post('cert/monitors/' . $_id . '/resume');
    }

    //
    // override to extract monitors from response
    //
    protected function getResourceName(): string
    {
        return 'monitors';
    }
}
