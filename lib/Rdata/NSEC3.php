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
use BadMethodCallException;
use Base32\Base32Hex;
use DomainException;
use InvalidArgumentException;

/**
 * {@link https://tools.ietf.org/html/rfc5155}.
 */
class NSEC3 implements RdataInterface
{
    use RdataTrait;

    public const TYPE = 'NSEC3';
    public const TYPE_CODE = 50;

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
     * @var int|null
     */
    private $iterations;

    /**
     * @var string|null Binary encoded string
     */
    private $salt;

    /**
     * @var string|null fully qualified next owner name
     */
    private $nextOwnerName;

    /**
     * @var string Binary encoded hash
     */
    private $nextHashedOwnerName;

    /**
     * @var array
     */
    private $types = [];

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

    public function getIterations(): ?int
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
    public function getSalt(): ?string
    {
        if (null === $this->salt) {
            return null;
        }

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

    public function getNextOwnerName(): ?string
    {
        return $this->nextOwnerName;
    }

    /**
     * Set the next owner name.
     *
     * @param string $nextOwnerName the fully qualified next owner name
     *
     * @throws InvalidArgumentException
     */
    public function setNextOwnerName(string $nextOwnerName): void
    {
        if (!Validator::fullyQualifiedDomainName($nextOwnerName)) {
            throw new InvalidArgumentException(sprintf('NSEC3: Next owner "%s" is not a fully qualified domain name.', $nextOwnerName));
        }
        $this->nextOwnerName = $nextOwnerName;
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

    public function toText(): string
    {
        return sprintf(
            '%d %d %d %s %s %s',
            $this->hashAlgorithm,
            (int) $this->unsignedDelegationsCovered,
            $this->iterations,
            empty($this->salt) ? '-' : $this->getSalt(),
            Base32Hex::encode($this->getNextHashedOwnerName()),
            implode(Tokens::SPACE, $this->types)
        );
    }

    /**
     * @throws UnsupportedTypeException
     */
    public function toWire(): string
    {
        $wire = pack(
            'CCnC',
            $this->hashAlgorithm,
            (int) $this->unsignedDelegationsCovered,
            $this->iterations,
            strlen($this->salt ?? '')
        );
        $wire .= $this->salt;
        $wire .= chr(strlen($this->nextHashedOwnerName));
        $wire .= $this->nextHashedOwnerName;
        $wire .= NSEC::renderBitmap($this->types);

        return $wire;
    }

    public function fromText(string $text): void
    {
        $rdata = explode(Tokens::SPACE, $text);
        $this->setHashAlgorithm((int) array_shift($rdata));
        $this->setUnsignedDelegationsCovered((bool) array_shift($rdata));
        $this->setIterations((int) array_shift($rdata));
        $salt = (string) array_shift($rdata);
        if ('-' === $salt) {
            $salt = '';
        }
        $this->setSalt($salt);
        $this->setNextHashedOwnerName(Base32Hex::decode(array_shift($rdata) ?? ''));
        array_map([$this, 'addType'], $rdata);
    }

    /**
     * @throws UnsupportedTypeException|DecodeException
     */
    public function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): void
    {
        if (false === $values = unpack('C<hashAlgo>/C<flags>/n<iterations>/C<saltLen>', $rdata, $offset)) {
            throw new DecodeException(static::TYPE, $rdata);
        }
        $offset += 5;
        $this->setHashAlgorithm((int) $values['<hashAlgo>']);
        $this->setUnsignedDelegationsCovered((bool) $values['<flags>']);
        $this->setIterations((int) $values['<iterations>']);

        $saltLen = (int) $values['<saltLen>'];
        if (false === $salt = unpack('H*', substr($rdata, $offset, $saltLen))) {
            throw new DecodeException(static::TYPE, $rdata);
        }
        $this->setSalt($salt[1]);
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
     * Calculate and set NSEC3::nextOwnerHash. Requires NSEC3::salt, NSEC3::nextOwnerName, and NSEC3::iterations to be set.
     *
     * @throws InvalidArgumentException
     */
    public function calculateNextOwnerHash(): void
    {
        if (!isset($this->nextOwnerName) || !isset($this->salt) || !isset($this->iterations)) {
            throw new BadMethodCallException('NSEC3::salt, NSEC3::nextOwnerName, and NSEC3::iterations must be set.');
        }
        $nextOwner = Message::encodeName(strtolower($this->nextOwnerName));
        $this->nextHashedOwnerName = self::hash($this->salt, $nextOwner, $this->iterations);
    }

    /**
     * @param string $salt the salt
     * @param string $x    the value to be hashed
     * @param int    $k    the number of recursive iterations of the hash function
     *
     * @return string the hashed value
     *
     * @throws DomainException
     */
    private static function hash(string $salt, string $x, int $k = 0): string
    {
        if ($k < 0) {
            throw new DomainException('Number of iterations, $k, must be a positive integer greater than, or equal to, 0.');
        }
        $x = sha1($x.$salt, true);
        if (0 === $k) {
            return $x;
        }
        --$k;

        return self::hash($salt, $x, $k);
    }
}
