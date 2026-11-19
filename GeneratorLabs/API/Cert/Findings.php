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
final class Findings
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
    // get the current findings on the active monitors
    //
    /**
     * @param array<string, mixed>|null $_settings
     * @param RequestOptions $_options
     */
    public function get(?array $_settings = null, array $_options = []): Response
    {
        return $this->_get('cert/findings', $_settings, $_options);
    }

    //
    // dismiss findings (finding_id, monitors); without monitors, from every active monitor
    // that has the finding
    //
    /**
     * @param array<string, mixed> $_data
     * @param RequestOptions $_options
     */
    public function dismiss(array $_data, array $_options = []): Response
    {
        return $this->_post('cert/findings/dismiss', $_data, $_options);
    }

    //
    // restore dismissed findings (finding_id, monitors); without monitors, on every monitor
    // the finding is dismissed from
    //
    /**
     * @param array<string, mixed> $_data
     * @param RequestOptions $_options
     */
    public function restore(array $_data, array $_options = []): Response
    {
        return $this->_post('cert/findings/restore', $_data, $_options);
    }

    //
    // override to extract the monitors and their findings from response
    //
    protected function getResourceName(): string
    {
        return 'data';
    }
}
