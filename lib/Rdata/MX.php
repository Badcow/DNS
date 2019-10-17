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
 * @see https://tools.ietf.org/html/rfc1035#section-3.3.9
 */
class MX implements RdataInterface
{
    use RdataTrait;

    const TYPE = 'MX';
    const TYPE_CODE = 15;

    /**
     * @var int|null
     */
    private $preference;

    /**
     * @var string|null
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
     * @return string|null
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
     * @return int|null
     */
    public function getPreference(): ?int
    {
        return $this->preference;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException throws exception if preference or exchange have not been set
     */
    public function output(): string
    {
        if (null === $this->preference) {
            throw new \InvalidArgumentException('No preference has been set on MX object.');
        }

        if (null === $this->exchange) {
            throw new \InvalidArgumentException('No exchange has been set on MX object.');
        }

        return $this->preference.' '.$this->exchange;
    }
}
