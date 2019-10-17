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

class NSEC implements RdataInterface
{
    use RdataTrait;

    const TYPE = 'NSEC';
    const TYPE_CODE = 47;

    /**
     * The Next Domain field contains the next owner name (in the canonical
     * ordering of the zone) that has authoritative data or contains a
     * delegation point NS RRset.
     * {@link https://tools.ietf.org/html/rfc4034#section-4.1.1}.
     *
     * @var string
     */
    private $nextDomainName;

    /**
     * @var array
     */
    private $types = [];

    /**
     * @return string
     */
    public function getNextDomainName(): string
    {
        return $this->nextDomainName;
    }

    /**
     * @param string $nextDomainName
     */
    public function setNextDomainName(string $nextDomainName): void
    {
        $this->nextDomainName = $nextDomainName;
    }

    /**
     * @param string $type
     */
    public function addType(string $type): void
    {
        $this->types[] = $type;
    }

    /**
     * @deprecated
     *
     * @param string $type
     */
    public function addTypeBitMap(string $type): void
    {
        @trigger_error('Method NSEC::addTypeBitMap has been deprecated. Use NSEC::addType instead.', E_USER_DEPRECATED);
        $this->addType($type);
    }

    /**
     * @deprecated
     */
    public function clearTypeMap(): void
    {
        @trigger_error('Method NSEC::clearTypeMap has been deprecated and is unusable. Use NSEC::clearTypes instead.', E_USER_DEPRECATED);
        $this->clearTypes();
    }

    /**
     * @deprecated
     */
    public function getTypeBitMaps()
    {
        @trigger_error('Method NSEC::getTypeBitMaps has been deprecated and is unusable. Use NSEC::getTypes instead.', E_USER_DEPRECATED);

        return$this->getTypes();
    }

    /**
     * Clears the types from the RDATA.
     */
    public function clearTypes(): void
    {
        $this->types = [];
    }

    /**
     * @return array
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * {@inheritdoc}
     */
    public function toText(): string
    {
        return sprintf(
            '%s %s',
            $this->nextDomainName,
            implode(' ', $this->types)
        );
    }

    public function toWire(): string
    {
        $blocks = [];

        foreach ($this->types as $type) {
            $int = TypeCodes::getTypeCode($type);
            $window = $int >> 8;
            $int = $int & 0b11111111;
            $mod = $int % 8;
            $mask = $blocks[$window] ?? str_repeat("\0", 32);
            $byteNum = ($int - $mod) / 8;
            $byte = ord($mask[$byteNum]) | (128 >> $mod);
            $mask[$byteNum] = chr($byte);
            $blocks[$window] = $mask;
        }

        $encoded = self::encodeName($this->nextDomainName);
        foreach ($blocks as $n => $mask) {
            $mask = rtrim($mask, "\0");
            $encoded .= chr($n).chr(strlen($mask)).$mask;
        }

        return $encoded;
    }

    public static function fromText(string $text): RdataInterface
    {
        $iterator = new \ArrayIterator(explode(Tokens::SPACE, $text));
        $nsec = new self();
        $nsec->setNextDomainName($iterator->current());
        $iterator->next();
        while ($iterator->valid()) {
            $nsec->addType($iterator->current());
            $iterator->next();
        }

        return $nsec;
    }

    public static function fromWire(string $rdata): RdataInterface
    {
        $nsec = new self();
        $offset = 0;
        $nsec->setNextDomainName(self::decodeName($rdata, $offset));

        $bytes = unpack('C*', $rdata, $offset);

        while (count($bytes) > 0) {
            $mask = '';
            $window = array_shift($bytes);
            $len = array_shift($bytes);
            for ($i = 0; $i < $len; ++$i) {
                $mask .= str_pad(decbin(array_shift($bytes)), 8, '0', STR_PAD_LEFT);
            }
            $offset = 0;
            while (false !== $pos = strpos($mask, '1', $offset)) {
                $nsec->addType(TypeCodes::getName((int) $window * 256 + $pos));
                $offset = $pos + 1;
            }
        }

        return $nsec;
    }
}
