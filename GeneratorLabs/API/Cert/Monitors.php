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

/**
 * @phpstan-import-type RequestOptions from \GeneratorLabs\Client
 */
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
    /**
     * @param string|array<string, mixed>|null $_id_or_settings
     * @param RequestOptions $_options
     */
    public function get(string|array|null $_id_or_settings = null, array $_options = []): Response
    {
        // If string, it's an ID for a single monitor
        if (is_string($_id_or_settings))
        {
            return $this->_get('cert/monitors/' . $_id_or_settings, null, $_options);
        }

        // Otherwise, it's query parameters for listing
        return $this->_get('cert/monitors', $_id_or_settings, $_options);
    }

    //
    // create a new monitor
    //
    /**
     * @param array<string, mixed> $_data
     * @param RequestOptions $_options
     */
    public function create(array $_data, array $_options = []): Response
    {
        return $this->_post('cert/monitors', $_data, $_options, true);
    }

    //
    // update a monitor
    //
    /**
     * @param array<string, mixed> $_data
     * @param RequestOptions $_options
     */
    public function update(string $_id, array $_data, array $_options = []): Response
    {
        return $this->_put('cert/monitors/' . $_id, $_data, $_options);
    }

    //
    // delete a monitor by id
    //
    /** @param RequestOptions $_options */
    public function delete(string $_id, array $_options = []): Response
    {
        return $this->_delete('cert/monitors/' . $_id, null, $_options, true);
    }

    //
    // pause a monitor by id
    //
    /** @param RequestOptions $_options */
    public function pause(string $_id, array $_options = []): Response
    {
        return $this->_post('cert/monitors/' . $_id . '/pause', null, $_options);
    }

    //
    // resume (un-pause) a monitor by id
    //
    /** @param RequestOptions $_options */
    public function resume(string $_id, array $_options = []): Response
    {
        return $this->_post('cert/monitors/' . $_id . '/resume', null, $_options);
    }

    //
    // queue a check of a monitor now
    //
    /** @param RequestOptions $_options */
    public function check(string $_id, array $_options = []): Response
    {
        return $this->_post('cert/monitors/' . $_id . '/check', null, $_options, true);
    }

    //
    // override to extract monitors from response
    //
    protected function getResourceName(): string
    {
        return 'monitors';
    }
}
