<?php declare(strict_types=1);

//
// This file is part of the Generator Labs PHP SDK package.
//
// (c) Generator Labs <support@generatorlabs.com>
//
// For the full copyright and license information, please view the LICENSE
// file that was distributed with this source code.
//

namespace GeneratorLabs\API;

use GeneratorLabs\Exception;

final class Contact
{
    private \GeneratorLabs\Client $m_client;

    private ?Contact\Contacts $contacts = null;
    private ?Contact\Groups $groups = null;

    //
    // constructor to store the client reference
    //
    public function __construct(\GeneratorLabs\Client $_client)
    {
        $this->m_client = $_client;
    }

    //
    // magic getter for Contact endpoints
    //
    public function __get(string $_name): mixed
    {
        return match ($_name) {
            'contacts' => $this->contacts ?? ($this->contacts = new Contact\Contacts($this->m_client)),
            'groups' => $this->groups ?? ($this->groups = new Contact\Groups($this->m_client)),
            default => throw new Exception('invalid Contact endpoint ' . $_name),
        };
    }
}
