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

class RateLimitInfo
{
    public function __construct(
        public readonly string $limit,
        public readonly int    $remaining,
        public readonly int    $reset
    ) {}
}
