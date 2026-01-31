<?php declare(strict_types=1);

//
// This file is part of the Generator Labs PHP SDK package.
//
// (c) Generator Labs <support@generatorlabs.com>
//
// For the full copyright and license information, please view the LICENSE
// file that was distributed with this source code.
//

namespace GeneratorLabs\API\Contact;

use GeneratorLabs\API\RequestHandler;

final class Contacts
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
    // get a list of contacts or a single contact by id
    //
    public function get(string|array|null $_id_or_settings = null): array
    {
        if (is_string($_id_or_settings)) {
            return $this->_get('contact/contacts/' . $_id_or_settings);
        }
        return $this->_get('contact/contacts', $_id_or_settings);
    }

    //
    // create a new contact
    //
    public function create(array $_data): array
    {
        return $this->_post('contact/contacts', $_data);
    }

    //
    // update a contact
    //
    public function update(string $_id, array $_data): array
    {
        return $this->_put('contact/contacts/' . $_id, $_data);
    }

    //
    // delete a contact by id
    //
    public function delete(string $_id): array
    {
        return $this->_delete('contact/contacts/' . $_id);
    }

    //
    // pause a contact by id
    //
    public function pause(string $_id): array
    {
        return $this->_post('contact/contacts/' . $_id . '/pause');
    }

    //
    // resume (un-pause) a contact by id
    //
    public function resume(string $_id): array
    {
        return $this->_post('contact/contacts/' . $_id . '/resume');
    }

    //
    // confirm a contact by id
    //
    public function confirm(string $_id, array $_data): array
    {
        return $this->_post('contact/contacts/' . $_id . '/confirm', $_data);
    }

    //
    // resend authorization code for a contact by id
    //
    public function resend(string $_id): array
    {
        return $this->_post('contact/contacts/' . $_id . '/resend');
    }
}
