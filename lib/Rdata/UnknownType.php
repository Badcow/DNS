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

use Badcow\DNS\Parser\ParseException;
use Badcow\DNS\Parser\Tokens;

/**
 * {@link https://tools.ietf.org/html/rfc3597}.
 */
class UnknownType implements RdataInterface
{
    /**
     * @var int
     */
    private $typeCode;

    /**
     * @var string|null
     */
    private $data;

    /**
     * @param int $typeCode
     */
    public function setTypeCode(int $typeCode): void
    {
        $this->typeCode = $typeCode;
    }

    /**
     * @return string
     */
    public function getData(): ?string
    {
        return $this->data;
    }

    /**
     * @param string $data
     */
    public function setData(?string $data): void
    {
        $this->data = $data;
    }

    public function getType(): string
    {
        return 'TYPE'.$this->typeCode;
    }

    public function getTypeCode(): int
    {
        return $this->typeCode;
    }

    public function toText(): string
    {
        if (null === $this->data) {
            return '\# 0';
        }

        return sprintf('\# %d %s', strlen($this->data), bin2hex($this->data));
    }

    public function toWire(): string
    {
        return $this->data ?? '';
    }

    /**
     * @param string $text
     *
     * @return UnknownType
     *
     * @throws ParseException
     */
    public static function fromText(string $text): RdataInterface
    {
        if (1 !== preg_match('/^\\\#\s+(\d+)(\s[a-f0-9\s]+)?$/i', $text, $matches)) {
            throw new ParseException('Could not parse rdata of unknown type. Malformed string.');
        }

        $rdata = new self();
        if ('0' === $matches[1]) {
            return $rdata;
        }

        $hexVal = str_replace(Tokens::SPACE, '', $matches[2]);

        if (false === $data = hex2bin($hexVal)) {
            throw new ParseException(sprintf('Could not parse hexadecimal data "%s".', $hexVal));
        }
        $rdata->setData($data);

        return $rdata;
    }

    /**
     * {@inheritdoc}
     *
     * @return UnknownType
     */
    public static function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): RdataInterface
    {
        $_rdata = new self();
        $_rdata->setData(substr($rdata, $offset, $rdLength ?? strlen($rdata)));

        return $_rdata;
    }
}
