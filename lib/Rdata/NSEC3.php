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
use Badcow\DNS\Validator;
use Base2n;
use DomainException;
use InvalidArgumentException;

/**
 * {@link https://tools.ietf.org/html/rfc5155}.
 */
class NSEC3 implements RdataInterface
{
    use RdataTrait;

    const TYPE = 'NSEC3';
    const TYPE_CODE = 50;

    /**
     * {@link https://www.iana.org/assignments/dnssec-nsec3-parameters/dnssec-nsec3-parameters.xhtml}.
     *
     * @var int the Hash Algorithm field identifies the cryptographic hash algorithm used to construct the hash-value
     */
    private $hashAlgorithm = 1;

    /**
     * @var bool
     */
    private $unsignedDelegationsCovered = false;

    /**
     * @var int
     */
    private $iterations;

    /**
     * @var string Binary encoded string
     */
    private $salt;

    /**
     * @var string Binary encoded hash
     */
    private $nextHashedOwnerName;

    /**
     * @var array
     */
    private $types = [];

    /**
     * @var Base2n
     */
    private static $base32;

    /**
     * Singleton to instantiate and return \Base2n instance for extended hex.
     */
    private static function base32(): Base2n
    {
        if (!isset(self::$base32)) {
            self::$base32 = new Base2n(5, '0123456789abcdefghijklmnopqrstuv', false, true, true);
        }

        return self::$base32;
    }

    public function getHashAlgorithm(): int
    {
        return $this->hashAlgorithm;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setHashAlgorithm(int $hashAlgorithm): void
    {
        if (!Validator::isUnsignedInteger($hashAlgorithm, 8)) {
            throw new InvalidArgumentException('Hash algorithm must be 8-bit integer.');
        }
        $this->hashAlgorithm = $hashAlgorithm;
    }

    public function isUnsignedDelegationsCovered(): bool
    {
        return $this->unsignedDelegationsCovered;
    }

    public function setUnsignedDelegationsCovered(bool $unsignedDelegationsCovered): void
    {
        $this->unsignedDelegationsCovered = $unsignedDelegationsCovered;
    }

    public function getIterations(): int
    {
        return $this->iterations;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setIterations(int $iterations): void
    {
        if (!Validator::isUnsignedInteger($iterations, 16)) {
            throw new InvalidArgumentException('Hash algorithm must be 16-bit integer.');
        }
        $this->iterations = $iterations;
    }

    /**
     * @return string Base16 string
     */
    public function getSalt(): string
    {
        return bin2hex($this->salt);
    }

    /**
     * @param string $salt Hexadecimal string
     */
    public function setSalt(string $salt): void
    {
        if (false === $bin = @hex2bin($salt)) {
            throw new InvalidArgumentException('Salt must be a hexadecimal string.');
        }
        $this->salt = $bin;
    }

    public function getNextHashedOwnerName(): string
    {
        return $this->nextHashedOwnerName;
    }

    public function setNextHashedOwnerName(string $nextHashedOwnerName): void
    {
        $this->nextHashedOwnerName = $nextHashedOwnerName;
    }

    public function addType(string $type): void
    {
        $this->types[] = $type;
    }

    public function setTypes(array $types): void
    {
        $this->types = $types;
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

    /**
     * {@inheritdoc}
     */
    public function toText(): string
    {
        return sprintf('%d %d %d %s %s %s',
            $this->hashAlgorithm,
            (int) $this->unsignedDelegationsCovered,
            $this->iterations,
            $this->getSalt(),
            self::base32encode($this->getNextHashedOwnerName()),
            implode(Tokens::SPACE, $this->types)
        );
    }

    /**
     * {@inheritdoc}
     *
     * @throws UnsupportedTypeException
     */
    public function toWire(): string
    {
        $wire = pack('CCnC',
            $this->hashAlgorithm,
            (int) $this->unsignedDelegationsCovered,
            $this->iterations,
            strlen($this->salt)
        );
        $wire .= $this->salt;
        $wire .= chr(strlen($this->nextHashedOwnerName));
        $wire .= $this->nextHashedOwnerName;
        $wire .= NSEC::renderBitmap($this->types);

        return $wire;
    }

    /**
     * {@inheritdoc}
     */
    public function fromText(string $text): void
    {
        $rdata = explode(Tokens::SPACE, $text);
        $this->setHashAlgorithm((int) array_shift($rdata));
        $this->setUnsignedDelegationsCovered((bool) array_shift($rdata));
        $this->setIterations((int) array_shift($rdata));
        $this->setSalt((string) array_shift($rdata));
        $this->setNextHashedOwnerName(self::base32decode(array_shift($rdata) ?? ''));
        array_map([$this, 'addType'], $rdata);
    }

    /**
     * {@inheritdoc}
     *
     * @throws UnsupportedTypeException|DecodeException
     */
    public function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): void
    {
        $values = unpack('C<hashAlgo>/C<flags>/n<iterations>/C<saltLen>', $rdata, $offset);
        $offset += 5;
        $this->setHashAlgorithm((int) $values['<hashAlgo>']);
        $this->setUnsignedDelegationsCovered((bool) $values['<flags>']);
        $this->setIterations((int) $values['<iterations>']);

        $saltLen = (int) $values['<saltLen>'];
        $salt = unpack('H*', substr($rdata, $offset, $saltLen))[1];
        $this->setSalt($salt);
        $offset += $saltLen;

        $hashLen = ord(substr($rdata, $offset, 1));
        ++$offset;
        $hash = substr($rdata, $offset, $hashLen);
        $offset += $hashLen;
        $this->setNextHashedOwnerName($hash);

        $types = NSEC::parseBitmap($rdata, $offset);
        array_map([$this, 'addType'], $types);
    }

    /**
     * Encode data as a base32 string.
     *
     * @return string base32 string
     */
    public static function base32encode(string $data): string
    {
        return self::base32()->encode($data);
    }

    /**
     * Decode a base32 encoded string.
     *
     * @param string $data base32 string
     */
    public static function base32decode(string $data): string
    {
        return self::base32()->decode($data);
    }
}
