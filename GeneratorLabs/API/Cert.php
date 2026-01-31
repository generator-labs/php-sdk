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

final class Cert
{
    private \GeneratorLabs\Client $m_client;

    private ?Cert\Errors $errors = null;
    private ?Cert\Monitors $monitors = null;
    private ?Cert\Profiles $profiles = null;

    //
    // constructor to store the client reference
    //
    public function __construct(\GeneratorLabs\Client $_client)
    {
        $this->m_client = $_client;
    }

    //
    // magic getter for Cert endpoints
    //
    public function __get(string $_name): mixed
    {
        return match ($_name) {
            'errors' => $this->errors ?? ($this->errors = new Cert\Errors($this->m_client)),
            'monitors' => $this->monitors ?? ($this->monitors = new Cert\Monitors($this->m_client)),
            'profiles' => $this->profiles ?? ($this->profiles = new Cert\Profiles($this->m_client)),
            default => throw new Exception('invalid Cert endpoint ' . $_name),
        };
    }
}
