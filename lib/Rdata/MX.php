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

class MX implements RdataInterface
{
    use RdataTrait;

    const TYPE = 'MX';

    /**
     * @var int
     */
    private $preference;

    /**
     * @var string
     */
    private $exchange;

    /**
     * @param string $exchange
     */
    public function setExchange(string $exchange): void
    {
        $this->exchange = $exchange;
    }

    /**
     * @return string
     */
    public function getExchange(): ?string
    {
        return $this->exchange;
    }

    /**
     * @param int $preference
     */
    public function setPreference(int $preference): void
    {
        $this->preference = $preference;
    }

    /**
     * @return int
     */
    public function getPreference(): ?int
    {
        return $this->preference;
    }

    /**
     * {@inheritdoc}
     */
    public function output(): string
    {
        return $this->preference.' '.$this->exchange;
    }
}
