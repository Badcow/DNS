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
use Badcow\DNS\Parser\Tokens;

class NSEC implements RdataInterface
{
    use RdataTrait;

    public const TYPE = 'NSEC';
    public const TYPE_CODE = 47;

    /**
     * The Next Domain field contains the next owner name (in the canonical
     * ordering of the zone) that has authoritative data or contains a
     * delegation point NS RR set.
     * {@link https://tools.ietf.org/html/rfc4034#section-4.1.1}.
     *
     * @var string
     */
    private $nextDomainName;

    /**
     * @var array
     */
    private $types = [];

    public function getNextDomainName(): string
    {
        return $this->nextDomainName;
    }

    public function setNextDomainName(string $nextDomainName): void
    {
        $this->nextDomainName = $nextDomainName;
    }

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

    public function toText(): string
    {
        return sprintf(
            '%s %s',
            $this->nextDomainName,
            implode(' ', $this->types)
        );
    }

    /**
     * @throws UnsupportedTypeException
     */
    public function toWire(): string
    {
        return Message::encodeName($this->nextDomainName).self::renderBitmap($this->types);
    }

    public function fromText(string $text): void
    {
        $iterator = new \ArrayIterator(explode(Tokens::SPACE, $text));
        $this->setNextDomainName($iterator->current());
        $iterator->next();
        while ($iterator->valid()) {
            $this->addType($iterator->current());
            $iterator->next();
        }
    }

    /**
     * @throws UnsupportedTypeException|DecodeException
     */
    public function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): void
    {
        $this->setNextDomainName(Message::decodeName($rdata, $offset));
        $types = self::parseBitmap($rdata, $offset);
        array_map([$this, 'addType'], $types);
    }

    /**
     * @return string[]
     *
     * @throws UnsupportedTypeException
     * @throws DecodeException
     */
    public static function parseBitmap(string $rdata, int &$offset): array
    {
        $bytes = unpack('C*', $rdata, $offset);

        if (!is_array($bytes)) {
            throw new DecodeException(static::TYPE, $rdata);
        }

        $types = [];

        while (count($bytes) > 0) {
            $mask = '';
            $window = array_shift($bytes);
            $len = array_shift($bytes);
            for ($i = 0; $i < $len; ++$i) {
                $mask .= str_pad(decbin(array_shift($bytes)), 8, '0', STR_PAD_LEFT);
            }
            $offset = 0;
            while (false !== $pos = strpos($mask, '1', $offset)) {
                $types[] = Types::getName((int) $window * 256 + $pos);
                $offset = $pos + 1;
            }
        }

        return $types;
    }

    /**
     * @param string[] $types
     *
     * @throws UnsupportedTypeException
     */
    public static function renderBitmap(array $types): string
    {
        /** @var string[] $blocks */
        $blocks = [];

        foreach ($types as $type) {
            $int = Types::getTypeCode($type);
            $window = $int >> 8;
            $int = $int & 0b11111111;
            $mod = $int % 8;
            $mask = $blocks[$window] ?? str_repeat("\0", 32);
            $byteNum = ($int - $mod) / 8;
            $byte = ord($mask[$byteNum]) | (128 >> $mod);
            $mask[$byteNum] = chr($byte);
            $blocks[$window] = $mask;
        }

        $encoded = '';
        foreach ($blocks as $n => $mask) {
            $mask = rtrim($mask, "\0");
            $encoded .= chr($n).chr(strlen($mask)).$mask;
        }

        return $encoded;
    }
}
