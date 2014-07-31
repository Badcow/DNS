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

class MxRdata implements RdataInterface
{
    const TYPE = "MX";

    /**
     * @var int
     */
    private $preference;

    /**
     * @var string
     */
    private $exchange;


    /**
     * @param $exchange
     * @return MxRdata
     * @throws RdataException
     */
    public function setExchange($exchange)
    {
        if (!Validator::validateFqdn($exchange)) {
            throw new RdataException(sprintf('The excahnge "%s" is not a Fully Qualified Domain Name', $exchange));
        }

        $this->exchange = $exchange;

        return $this;
    }

    /**
     * @return string
     */
    public function getExchange()
    {
        return $this->exchange;
    }

    /**
     * @param $preference
     * @return MxRdata
     */
    public function setPreference($preference)
    {
        $this->preference = (int) $preference;

        return $this;
    }

    /**
     * @return int
     */
    public function getPreference()
    {
        return $this->preference;
    }

    /**
     * {@inheritdoc}
     */
    public function getLength()
    {
        return strlen((string) $this);
    }

    /**
     * {@inheritdoc}
     */
    public function output()
    {
        return $this->preference . ' ' . $this->exchange;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE;
    }
}