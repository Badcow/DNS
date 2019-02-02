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
 * @see https://tools.ietf.org/html/rfc1035#section-3.3.2
 */
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
    public function setCpu(string $cpu): void
    {
        $this->cpu = $cpu;
    }

    /**
     * @return string
     */
    public function getCpu(): ?string
    {
        return $this->cpu;
    }

    /**
     * @param string $os
     */
    public function setOs(string $os): void
    {
        $this->os = $os;
    }

    /**
     * @return string
     */
    public function getOs(): ?string
    {
        return $this->os;
    }

    /**
     * {@inheritdoc}
     */
    public function output(): string
    {
        return sprintf('"%s" "%s"', $this->cpu, $this->os);
    }
}
