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

class HinfoRdata implements RdataInterface
{
    const TYPE = "HINFO";

    /**
     * @var string
     */
    private $cpu;

    /**
     * @var string
     */
    private $os;


    /**
     * @param $cpu
     * @return HinfoRdata
     */
    public function setCpu($cpu)
    {
        $this->cpu = (string) $cpu;

        return $this;
    }

    /**
     * @return string
     */
    public function getCpu()
    {
        return $this->cpu;
    }

    /**
     * @param $os
     * @return HinfoRdata
     */
    public function setOs($os)
    {
        $this->os = (string) $os;

        return $this;
    }

    /**
     * @return string
     */
    public function getOs()
    {
        return $this->os;
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
        return '"' . $this->cpu . '" "' . $this->os . '"';
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE;
    }
}
