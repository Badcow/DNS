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
    const TYPE = 'AAAA';
    const TYPE_CODE = 28;

    /**
     * @param string $address
     */
    public function setAddress(string $address): void
    {
        if (!Validator::ipv6($address)) {
            throw new \InvalidArgumentException(sprintf('The address "%s" is not a valid IPv6 address.', $address));
        }

        $this->address = $address;
    }

    /**
     * {@inheritdoc}
     *
     * @throws DecodeException
     */
    public static function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): RdataInterface
    {
        if (false === $address = @inet_ntop(substr($rdata, $offset, 16))) {
            throw new DecodeException(static::TYPE, $rdata);
        }
        $offset += 16;

        $a = new self();
        $a->setAddress($address);

        return $a;
    }
}
