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

class Response implements \ArrayAccess
{
    public readonly ?RateLimitInfo $rate_limit;
    private array $m_data;

    public function __construct(array $_data, ?RateLimitInfo $_rate_limit = null)
    {
        $this->m_data     = $_data;
        $this->rate_limit = $_rate_limit;
    }

    //
    // ArrayAccess interface
    //

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->m_data[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->m_data[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->m_data[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->m_data[$offset]);
    }

    //
    // convert back to a plain array
    //
    public function toArray(): array
    {
        return $this->m_data;
    }
}
