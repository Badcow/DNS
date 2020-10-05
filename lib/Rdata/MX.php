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

use Badcow\DNS\Message;

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

    public function setExchange(string $exchange): void
    {
        $this->exchange = $exchange;
    }

    public function getExchange(): ?string
    {
        return $this->exchange;
    }

    public function setPreference(int $preference): void
    {
        $this->preference = $preference;
    }

    public function getPreference(): ?int
    {
        return $this->preference;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException throws exception if preference or exchange have not been set
     */
    public function toText(): string
    {
        if (null === $this->preference) {
            throw new \InvalidArgumentException('No preference has been set on MX object.');
        }

        if (null === $this->exchange) {
            throw new \InvalidArgumentException('No exchange has been set on MX object.');
        }

        return $this->preference.' '.$this->exchange;
    }

    /**
     * {@inheritdoc}
     */
    public function toWire(): string
    {
        if (null === $this->preference) {
            throw new \InvalidArgumentException('No preference has been set on MX object.');
        }

        if (null === $this->exchange) {
            throw new \InvalidArgumentException('No exchange has been set on MX object.');
        }

        return pack('n', $this->preference).Message::encodeName($this->exchange);
    }

    /**
     * {@inheritdoc}
     */
    public function fromText(string $text): void
    {
        $rdata = explode(' ', $text);
        $this->setPreference((int) $rdata[0]);
        $this->setExchange($rdata[1]);
    }

    /**
     * {@inheritdoc}
     */
    public function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): void
    {
        $this->setPreference(unpack('n', $rdata, $offset)[1]);
        $offset += 2;
        $this->setExchange(Message::decodeName($rdata, $offset));
    }
}
