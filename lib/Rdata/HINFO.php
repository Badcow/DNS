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

class HINFO implements RdataInterface
{
    use RdataTrait;

    const TYPE = 'HINFO';

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
     */
    public function setCpu($cpu)
    {
        $this->cpu = (string) $cpu;
    }

    /**
     * @return string
     */
    public function getCpu()
    {
        return $this->cpu;
    }

    /**
     * @param string $os
     */
    public function setOs($os)
    {
        $this->os = (string) $os;
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
    public function output()
    {
        return sprintf('"%s" "%s"', $this->cpu, $this->os);
    }
}
