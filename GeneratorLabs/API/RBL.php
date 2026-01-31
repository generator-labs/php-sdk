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

final class RBL
{
    private \GeneratorLabs\Client $m_client;

    private ?RBL\Hosts $hosts = null;
    private ?RBL\Check $check = null;
    private ?RBL\Profiles $profiles = null;
    private ?RBL\Sources $sources = null;
    private ?RBL\Listings $listings = null;

    //
    // constructor to store the client reference
    //
    public function __construct(\GeneratorLabs\Client $_client)
    {
        $this->m_client = $_client;
    }

    //
    // magic getter for RBL endpoints
    //
    public function __get(string $_name): mixed
    {
        return match ($_name) {
            'hosts' => $this->hosts ?? ($this->hosts = new RBL\Hosts($this->m_client)),
            'check' => $this->check ?? ($this->check = new RBL\Check($this->m_client)),
            'profiles' => $this->profiles ?? ($this->profiles = new RBL\Profiles($this->m_client)),
            'sources' => $this->sources ?? ($this->sources = new RBL\Sources($this->m_client)),
            'listings' => $this->listings ?? ($this->listings = new RBL\Listings($this->m_client)),
            default => throw new Exception('invalid RBL endpoint ' . $_name),
        };
    }
}
