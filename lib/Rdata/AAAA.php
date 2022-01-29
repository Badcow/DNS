<?php

declare(strict_types=1);

/*
 * This file is part of Badcow DNS Library.
 *
 * (c) Samuel Williams <sam@badcow.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Badcow\DNS\Rdata;

use Badcow\DNS\Validator;

/**
 * @see https://tools.ietf.org/html/rfc3596#section-2.1
 */
class AAAA extends A
{
    public const TYPE = 'AAAA';
    public const TYPE_CODE = 28;

    public function setAddress(string $address): void
    {
        if (!Validator::ipv6($address)) {
            throw new \InvalidArgumentException(sprintf('The address "%s" is not a valid IPv6 address.', $address));
        }

        $this->address = $address;
    }

    /**
     * @throws DecodeException
     */
    public function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): void
    {
        if (false === $address = @inet_ntop(substr($rdata, $offset, 16))) {
            throw new DecodeException(static::TYPE, $rdata);
        }
        $offset += 16;

        $this->setAddress($address);
    }
}
