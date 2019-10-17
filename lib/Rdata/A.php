<?php

/*
 * This file is part of Badcow DNS Library.
 *
 * (c) Samuel Williams <sam@badcow.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Badcow\DNS\Rdata;

/**
 * @see https://tools.ietf.org/html/rfc1035#section-3.4.1
 */
class A implements RdataInterface
{
    use RdataTrait;

    const TYPE = 'A';
    const TYPE_CODE = 1;

    /**
     * @var string|null
     */
    protected $address;

    /**
     * @param string $address
     */
    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    /**
     * @return string
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * {@inheritdoc}
     */
    public function toText(): string
    {
        return $this->address ?? '';
    }

    /**
     * {@inheritDoc}
     */
    public function toWire(): string
    {
        return inet_pton($this->address);
    }

    /**
     * {@inheritDoc}
     */
    public static function fromText(string $text): RdataInterface
    {
        $a = new self;
        $a->setAddress($text);

        return $a;
    }

    /**
     * {@inheritDoc}
     */
    public static function fromWire(string $rdata): RdataInterface
    {
        $a = new self;
        $a->setAddress(inet_ntop($rdata));

        return $a;
    }
}
