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
    public function setAddress(string $address)
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
    public function output(): string
    {
        return $this->address ?? '';
    }
}
