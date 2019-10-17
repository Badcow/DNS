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

/**
 * @see https://tools.ietf.org/html/rfc1035#section-3.4.1
 */
class A implements RdataInterface
{
    use RdataTrait;

    const TYPE = 'A';
    const TYPE_CODE = 1;

    /**
     * @var string
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
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException
     */
    public function toWire(): string
    {
        if (false === $encoded = inet_pton($this->address)) {
            throw new \InvalidArgumentException(sprintf('The IP address "%s" cannot be encoded. Check that it is a valid IP address.', $this->address));
        }

        return $encoded;
    }

    /**
     * {@inheritdoc}
     */
    public static function fromText(string $text): RdataInterface
    {
        $a = new static();
        $a->setAddress($text);

        return $a;
    }

    /**
     * {@inheritdoc}
     */
    public static function fromWire(string $rdata): RdataInterface
    {
        if (false === $address = inet_ntop($rdata)) {
            throw new \InvalidArgumentException('The IP address cannot be decoded.');
        }

        $a = new static();
        $a->setAddress($address);

        return $a;
    }
}
