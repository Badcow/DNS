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

/**
 * {@link https://tools.ietf.org/html/rfc2230}.
 */
class KX implements RdataInterface
{
    use RdataTrait;

    const TYPE = 'KX';
    const TYPE_CODE = 36;

    /**
     * @var int
     */
    private $preference;

    /**
     * @var string
     */
    private $exchanger;

    /**
     * @param string $exchanger
     */
    public function setExchanger(string $exchanger): void
    {
        $this->exchanger = $exchanger;
    }

    /**
     * @return string
     */
    public function getExchanger(): string
    {
        return $this->exchanger;
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
    public function getPreference(): int
    {
        return $this->preference;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException throws exception if preference or exchanger have not been set
     */
    public function toText(): string
    {
        if (null === $this->preference) {
            throw new \InvalidArgumentException('No preference has been set on KX object.');
        }
        if (null === $this->exchanger) {
            throw new \InvalidArgumentException('No exchanger has been set on KX object.');
        }

        return $this->preference.' '.$this->exchanger;
    }

    /**
     * {@inheritdoc}
     */
    public function toWire(): string
    {
        if (null === $this->preference) {
            throw new \InvalidArgumentException('No preference has been set on KX object.');
        }
        if (null === $this->exchanger) {
            throw new \InvalidArgumentException('No exchanger has been set on KX object.');
        }

        return pack('n', $this->preference).self::encodeName($this->exchanger);
    }

    /**
     * {@inheritdoc}
     */
    public static function fromText(string $text): RdataInterface
    {
        $rdata = explode(' ', $text);
        $kx = new self();
        $kx->setPreference((int) $rdata[0]);
        $kx->setExchanger($rdata[1]);

        return $kx;
    }

    /**
     * {@inheritdoc}
     */
    public static function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): RdataInterface
    {
        $kx = new self();
        $kx->setPreference(unpack('n', $rdata, $offset)[1]);
        $offset += 2;
        $kx->setExchanger(self::decodeName($rdata, $offset));

        return $kx;
    }
}
