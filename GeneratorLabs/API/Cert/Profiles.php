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
    public function get(string|array|null $_id_or_settings = null): Response
    {
        // If string, it's an ID for a single profile
        if (is_string($_id_or_settings))
        {
            return $this->_get('cert/profiles/' . $_id_or_settings);
        }

        // Otherwise, it's query parameters for listing
        return $this->_get('cert/profiles', $_id_or_settings);
    }

    //
    // create a new profile
    //
    public function create(array $_data): Response
    {
        return $this->_post('cert/profiles', $_data);
    }

    //
    // update a profile
    //
    public function update(string $_id, array $_data): Response
    {
        return $this->_put('cert/profiles/' . $_id, $_data);
    }

    //
    // delete a profile by id
    //
    public function delete(string $_id): Response
    {
        return $this->_delete('cert/profiles/' . $_id);
    }

    //
    // override to extract profiles from response
    //
    protected function getResourceName(): string
    {
        return 'profiles';
    }
}
