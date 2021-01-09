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

    public function getSubType(): int
    {
        return $this->subType;
    }

    public function setSubType(int $subType): void
    {
        $this->subType = $subType;
    }

    public function getHostname(): string
    {
        return $this->hostname;
    }

    public function setHostname(string $hostname): void
    {
        $this->hostname = $hostname;
    }

    /**
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

    public function toWire(): string
    {
        return pack('n', $this->subType).Message::encodeName($this->hostname);
    }

    public function fromText(string $text): void
    {
        $rdata = explode(' ', $text);

        $this->setSubType((int) $rdata[0]);
        $this->setHostname($rdata[1]);
    }

    public function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): void
    {
        if (false === $subType = unpack('n', $rdata, $offset)) {
            throw new DecodeException(static::TYPE, $rdata);
        }
        $this->setSubType($subType[1]);
        $offset += 2;
        $this->setHostname(Message::decodeName($rdata, $offset));
    }
}
