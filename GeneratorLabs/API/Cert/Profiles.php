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
final class Profiles
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
    // get a list of profiles, or a single profile by id
    //
    /**
     * @param string|array<string, mixed>|null $_id_or_settings
     * @param RequestOptions $_options
     */
    public function get(string|array|null $_id_or_settings = null, array $_options = []): Response
    {
        // If string, it's an ID for a single profile
        if (is_string($_id_or_settings))
        {
            return $this->_get('cert/profiles/' . $_id_or_settings, null, $_options);
        }

        // Otherwise, it's query parameters for listing
        return $this->_get('cert/profiles', $_id_or_settings, $_options);
    }

    //
    // create a new profile
    //
    /**
     * @param array<string, mixed> $_data
     * @param RequestOptions $_options
     */
    public function create(array $_data, array $_options = []): Response
    {
        return $this->_post('cert/profiles', $_data, $_options, true);
    }

    //
    // update a profile
    //
    /**
     * @param array<string, mixed> $_data
     * @param RequestOptions $_options
     */
    public function update(string $_id, array $_data, array $_options = []): Response
    {
        return $this->_put('cert/profiles/' . $_id, $_data, $_options);
    }

    //
    // delete a profile by id
    //
    /** @param RequestOptions $_options */
    public function delete(string $_id, array $_options = []): Response
    {
        return $this->_delete('cert/profiles/' . $_id, null, $_options);
    }

    //
    // override to extract profiles from response
    //
    protected function getResourceName(): string
    {
        return 'profiles';
    }
}
