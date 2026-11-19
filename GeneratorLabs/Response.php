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

/**
 * @implements \ArrayAccess<string, mixed>
 */
class Response implements \ArrayAccess
{
    public readonly ?RateLimitInfo $rate_limit;

    //
    // the X-Request-Id of the response; quote it when contacting support
    //
    public readonly ?string $request_id;

    //
    // the ETag of a GET response, to send back as the if_none_match option
    //
    public readonly ?string $etag;

    //
    // true when the API answered with the result of an earlier request that carried the
    // same Idempotency-Key
    //
    public readonly bool $replayed;

    //
    // true for a 304 Not Modified answer to if_none_match (no data)
    //
    public readonly bool $not_modified;

    /** @var array<string, mixed> */
    private array $m_data;

    /** @param array<string, mixed> $_data */
    public function __construct(
        array $_data,
        ?RateLimitInfo $_rate_limit = null,
        ?string $_request_id = null,
        ?string $_etag = null,
        bool $_replayed = false,
        bool $_not_modified = false
    ) {
        $this->m_data       = $_data;
        $this->rate_limit   = $_rate_limit;
        $this->request_id   = $_request_id;
        $this->etag         = $_etag;
        $this->replayed     = $_replayed;
        $this->not_modified = $_not_modified;
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
    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return $this->m_data;
    }
}
