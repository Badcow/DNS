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

class AFSDB implements RdataInterface
{
    use RdataTrait;

    const TYPE = 'AFSDB';
    const TYPE_CODE = 18;

    /**
     * @var int
     */
    private $subType;

    /**
     * @var string
     */
    private $hostname;

    /**
     * @return int
     */
    public function getSubType(): int
    {
        return $this->subType;
    }

    /**
     * @param int $subType
     */
    public function setSubType(int $subType): void
    {
        $this->subType = $subType;
    }

    /**
     * @return string
     */
    public function getHostname(): string
    {
        return $this->hostname;
    }

    /**
     * @param string $hostname
     */
    public function setHostname(string $hostname): void
    {
        $this->hostname = $hostname;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException
     */
    public function toText(): string
    {
        if (!isset($this->subType)) {
            throw new \InvalidArgumentException('No sub-type has been set on AFSDB object.');
        }

        if (!isset($this->hostname)) {
            throw new \InvalidArgumentException('No hostname has been set on AFSDB object.');
        }

        return sprintf('%d %s', $this->subType, $this->hostname);
    }

    /**
     * {@inheritdoc}
     */
    public function toWire(): string
    {
        return pack('n', $this->subType).self::encodeName($this->hostname);
    }

    /**
     * {@inheritdoc}
     */
    public static function fromText(string $text): RdataInterface
    {
        $rdata = explode(' ', $text);
        $afsdb = new self();
        $afsdb->setSubType((int) $rdata[0]);
        $afsdb->setHostname($rdata[1]);

        return $afsdb;
    }

    /**
     * {@inheritdoc}
     */
    public static function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): RdataInterface
    {
        $afsdb = new self();
        $afsdb->setSubType(unpack('n', $rdata, $offset)[1]);
        $offset += 2;
        $afsdb->setHostname(self::decodeName($rdata, $offset));

        return $afsdb;
    }
}
