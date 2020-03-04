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
 * {@link https://tools.ietf.org/html/rfc5155}.
 */
class NSEC3 implements RdataInterface
{
    use RdataTrait;

    const TYPE = 'NSEC3';
    const TYPE_CODE = 50;

    /**
     * @var int
     */
    private $hashAlgorithm;

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
     * @var \Base2n
     */
    private static $base32;

    /**
     * Singleton to instantiate and return \Base2n instance for extended hex.
     *
     * @return \Base2n
     */
    private static function base32(): \Base2n
    {
        if (!isset(self::$base32)) {
            self::$base32 = new \Base2n(5, '0123456789abcdefghijklmnopqrstuv', false, true, true);
        }

        return self::$base32;
    }

    /**
     * @return int
     */
    public function getHashAlgorithm(): int
    {
        return $this->hashAlgorithm;
    }

    /**
     * @param int $hashAlgorithm
     *
     * @throws \InvalidArgumentException
     */
    public function setHashAlgorithm(int $hashAlgorithm): void
    {
        if (!Validator::isUnsignedInteger($hashAlgorithm, 8)) {
            throw new \InvalidArgumentException('Hash algorithm must be 8-bit integer.');
        }
        $this->hashAlgorithm = $hashAlgorithm;
    }

    /**
     * @return bool
     */
    public function isUnsignedDelegationsCovered(): bool
    {
        return $this->unsignedDelegationsCovered;
    }

    /**
     * @param bool $unsignedDelegationsCovered
     */
    public function setUnsignedDelegationsCovered(bool $unsignedDelegationsCovered): void
    {
        $this->unsignedDelegationsCovered = $unsignedDelegationsCovered;
    }

    /**
     * @return int
     */
    public function getIterations(): int
    {
        return $this->iterations;
    }

    /**
     * @param int $iterations
     *
     * @throws \InvalidArgumentException
     */
    public function setIterations(int $iterations): void
    {
        if (!Validator::isUnsignedInteger($iterations, 16)) {
            throw new \InvalidArgumentException('Hash algorithm must be 16-bit integer.');
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
            throw new \InvalidArgumentException('Salt must be a hexadecimal string.');
        }
        $this->salt = $bin;
    }

    /**
     * @return string Base32 hashed string
     */
    public function getNextHashedOwnerName(): string
    {
        return self::base32encode($this->nextHashedOwnerName);
    }

    /**
     * @param string $nextHashedOwnerName
     */
    public function setNextHashedOwnerName(string $nextHashedOwnerName): void
    {
        if (!Validator::isBase32HexEncoded($nextHashedOwnerName)) {
            throw new \InvalidArgumentException('Next hashed owner name must be a base32 encoded string.');
        }

        $this->nextHashedOwnerName = self::base32decode($nextHashedOwnerName);
    }

    /**
     * @param string $type
     */
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
        return sprintf('%d %d %d %s %s %s',
            $this->hashAlgorithm,
            (int) $this->unsignedDelegationsCovered,
            $this->iterations,
            $this->getSalt(),
            $this->getNextHashedOwnerName(),
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
     *
     * @return NSEC3
     */
    public static function fromText(string $text): RdataInterface
    {
        $rdata = explode(Tokens::SPACE, $text);
        $nsec3 = new self();
        $nsec3->setHashAlgorithm((int) array_shift($rdata));
        $nsec3->setUnsignedDelegationsCovered((bool) array_shift($rdata));
        $nsec3->setIterations((int) array_shift($rdata));
        $nsec3->setSalt((string) array_shift($rdata));
        $nsec3->setNextHashedOwnerName((string) array_shift($rdata));
        array_map([$nsec3, 'addType'], $rdata);

        return $nsec3;
    }

    /**
     * {@inheritdoc}
     *
     * @return NSEC3
     *
     * @throws UnsupportedTypeException
     */
    public static function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): RdataInterface
    {
        $values = unpack('C<hashAlgo>/C<flags>/n<iterations>/C<saltLen>', $rdata, $offset);
        $offset += 5;
        $nsec3 = new self();
        $nsec3->setHashAlgorithm((int) $values['<hashAlgo>']);
        $nsec3->setUnsignedDelegationsCovered((bool) $values['<flags>']);
        $nsec3->setIterations((int) $values['<iterations>']);

        $saltLen = (int) $values['<saltLen>'];
        $salt = unpack('H*', substr($rdata, $offset, $saltLen))[1];
        $nsec3->setSalt($salt);
        $offset += $saltLen;

        $hashLen = ord(substr($rdata, $offset, 1));
        ++$offset;
        $hash = substr($rdata, $offset, $hashLen);
        $offset += $hashLen;
        $nsec3->setNextHashedOwnerName(self::base32encode($hash));

        $types = NSEC::parseBitmap($rdata, $offset);
        array_map([$nsec3, 'addType'], $types);

        return $nsec3;
    }

    /**
     * Encode data as a base32 string.
     *
     * @param string $data
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
     *
     * @return string
     */
    public static function base32decode(string $data): string
    {
        return self::base32()->decode($data);
    }
}
