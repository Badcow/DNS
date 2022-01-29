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
use Badcow\DNS\Validator;

/**
 * Class CaaRdata.
 *
 * CAA is defined in RFC 6844
 *
 * @see https://tools.ietf.org/html/rfc6844
 *
 * @author Samuel Williams <sam@badcow.co>
 */
class CAA implements RdataInterface
{
    use RdataTrait;

    public const TYPE = 'CAA';
    public const TYPE_CODE = 257;

    public const TAG_ISSUE = 'issue';
    public const TAG_ISSUEWILD = 'issuewild';
    public const TAG_IODEF = 'iodef';
    public const ALLOWED_TAGS = [self::TAG_ISSUE, self::TAG_ISSUEWILD, self::TAG_IODEF];

    /**
     * It is currently used to represent the critical flag.
     *
     * @var int|null
     */
    private $flag;

    /**
     * An ASCII string that represents the identifier of the property represented by the record.
     * The RFC currently defines 3 available tags:
     *  - issue: explicitly authorizes a single certificate authority to issue a certificate (any type) for the hostname.
     *  - issuewild: explicitly authorizes a single certificate authority to issue a wildcard certificate (and only wildcard) for the hostname.
     *  - iodef: specifies a URL to which a certificate authority may report policy violations.
     *
     * @var string|null
     */
    private $tag;

    /**
     * @var string|null
     */
    private $value;

    /**
     * @return int
     */
    public function getFlag(): ?int
    {
        return $this->flag;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function setFlag(int $flag): void
    {
        if (!Validator::isUnsignedInteger($flag, 8)) {
            throw new \InvalidArgumentException('Flag must be an unsigned 8-bit integer.');
        }

        $this->flag = $flag;
    }

    /**
     * @return string
     */
    public function getTag(): ?string
    {
        return $this->tag;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function setTag(string $tag): void
    {
        $tag = strtolower($tag);
        if (!in_array($tag, static::ALLOWED_TAGS)) {
            throw new \InvalidArgumentException('Tag can be one of this type "issue", "issuewild", or "iodef".');
        }

        $this->tag = $tag;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    public function toText(): string
    {
        if (!isset($this->tag) || !isset($this->flag) || !isset($this->value)) {
            throw new \InvalidArgumentException('All CAA parameters must be set.');
        }

        return sprintf(
            '%d %s "%s"',
            $this->flag,
            $this->tag ?? '',
            $this->value ?? ''
        );
    }

    public function toWire(): string
    {
        if (!isset($this->tag) || !isset($this->flag) || !isset($this->value)) {
            throw new \InvalidArgumentException('All CAA parameters must be set.');
        }

        return chr($this->flag).
            chr(strlen($this->tag)).
            $this->tag.
            $this->value;
    }

    public function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): void
    {
        $this->setFlag(ord($rdata[$offset]));
        ++$offset;

        $tagLen = ord($rdata[$offset]);
        ++$offset;

        $this->setTag(substr($rdata, $offset, $tagLen));
        $offset += $tagLen;

        $valueLen = ($rdLength ?? strlen($rdata)) - 2 - $tagLen;
        $this->setValue(substr($rdata, $offset, $valueLen));

        $offset = $offset += $valueLen;
    }

    public function fromText(string $text): void
    {
        $rdata = explode(Tokens::SPACE, $text);
        $this->setFlag((int) array_shift($rdata));
        $this->setTag((string) array_shift($rdata));
        $rdata = implode('', $rdata);
        $this->setValue(trim($rdata, '"'));
    }
}
