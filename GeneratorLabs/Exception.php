<?php declare(strict_types=1);

//
// This file is part of the Generator Labs PHP SDK package.
//
// (c) Generator Labs <support@generatorlabs.com>
//
// For the full copyright and license information, please view the LICENSE
// file that was distributed with this source code.
//

namespace GeneratorLabs;

final class Exception extends \Exception
{
    //
    // the X-Request-Id of the failed request, when the API answered; quote it when
    // contacting support
    //
    public readonly ?string $request_id;

    public function __construct(string $_message = '', int $_code = 0, ?string $_request_id = null)
    {
        parent::__construct($_message, $_code);

        $this->request_id = $_request_id;
    }
}
