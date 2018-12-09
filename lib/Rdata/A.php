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

class A implements RdataInterface
{
    use RdataTrait;

    const TYPE = 'A';

    /**
     * @var string
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
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * {@inheritdoc}
     */
    public function output(): string
    {
        return $this->address;
    }
}
