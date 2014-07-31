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

use Badcow\DNS\Validator;

class AaaaRdata implements RdataInterface
{
    const TYPE = "AAAA";

    /**
     * @var string
     */
    private $address;

    /**
     * @param $address
     * @return AaaaRdata
     * @throws RdataException
     */
    public function setAddress($address)
    {
        if (!Validator::validateIpv6Address($address)) {
            throw new RdataException(sprintf('Address "%s" is not a valid IPv6 address', $address));
        }

        $this->address = $address;

        return $this;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * {@inheritdoc}
     */
    public function getLength()
    {
        return strlen($this->address);
    }

    /**
     * {@inheritdoc}
     */
    public function output()
    {
        return $this->address;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE;
    }
}