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
 * @see https://tools.ietf.org/html/rfc1035#section-3.4.1
 */
class A implements RdataInterface
{
    use RdataTrait;

    public const TYPE = 'A';
    public const TYPE_CODE = 1;

    /**
     * @var string|null
     */
    protected $address;

    public function setAddress(string $address): void
    {
        if (!Validator::ipv4($address)) {
            throw new \InvalidArgumentException(sprintf('The address "%s" is not a valid IPv4 address.', $address));
        }

        $this->address = $address;
    }

    /**
     * @return string
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function toText(): string
    {
        return $this->address ?? '';
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function toWire(): string
    {
        if (!isset($this->address)) {
            throw new \InvalidArgumentException('No IP address has been set.');
        }

        if (false === $encoded = @inet_pton($this->address)) {
            throw new \InvalidArgumentException(sprintf('The IP address "%s" cannot be encoded. Check that it is a valid IP address.', $this->address));
        }

        return $encoded;
    }

    public function fromText(string $text): void
    {
        $this->setAddress($text);
    }

    /**
     * @throws DecodeException
     */
    public function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): void
    {
        if (false === $address = @inet_ntop(substr($rdata, $offset, 4))) {
            throw new DecodeException(static::TYPE, $rdata);
        }
        $offset += 4;

        $this->setAddress($address);
    }
}
