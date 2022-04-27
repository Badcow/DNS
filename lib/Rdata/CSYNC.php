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

use Badcow\DNS\Parser\Tokens;

/**
 * {@link https://tools.ietf.org/html/rfc7477}.
 */
class CSYNC implements RdataInterface
{
    use RdataTrait;

    public const TYPE = 'CSYNC';
    public const TYPE_CODE = 62;

    /**
     * @var int
     */
    private $soaSerial;

    /**
     * @var int
     */
    private $flags;

    /**
     * @var array
     */
    private $types = [];

    public function addType(string $type): void
    {
        $this->types[] = $type;
    }

    /**
     * Clears the types from the RDATA.
     */
    public function clearTypes(): void
    {
        $this->types = [];
    }

    public function getTypes(): array
    {
        return $this->types;
    }

    public function getSoaSerial(): int
    {
        return $this->soaSerial;
    }

    public function setSoaSerial(int $soaSerial): void
    {
        $this->soaSerial = $soaSerial;
    }

    public function getFlags(): int
    {
        return $this->flags;
    }

    public function setFlags(int $flags): void
    {
        $this->flags = $flags;
    }

    public function toText(): string
    {
        return sprintf('%d %d %s', $this->soaSerial, $this->flags, implode(Tokens::SPACE, $this->types));
    }

    /**
     * @throws UnsupportedTypeException
     */
    public function toWire(): string
    {
        return pack('Nn', $this->soaSerial, $this->flags).NSEC::renderBitmap($this->types);
    }

    public function fromText(string $text): void
    {
        $rdata = explode(Tokens::SPACE, $text);
        $this->setSoaSerial((int) array_shift($rdata));
        $this->setFlags((int) array_shift($rdata));
        array_map([$this, 'addType'], $rdata);
    }

    /**
     * @throws UnsupportedTypeException|DecodeException
     */
    public function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): void
    {
        if (false === $integers = unpack('Nserial/nflags', $rdata, $offset)) {
            throw new DecodeException(static::TYPE, $rdata);
        }
        $offset += 6;
        $types = NSEC::parseBitmap($rdata, $offset);

        $this->setSoaSerial((int) $integers['serial']);
        $this->setFlags((int) $integers['flags']);
        array_map([$this, 'addType'], $types);
    }
}
