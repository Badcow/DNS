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
 * {@link https://tools.ietf.org/html/rfc5155#section-4}.
 */
class NSEC3PARAM implements RdataInterface
{
    use RdataTrait;

    const TYPE = 'NSEC3PARAM';
    const TYPE_CODE = 51;

    /**
     * @var int
     */
    private $hashAlgorithm;

    /**
     * @var int
     */
    private $flags = 0;

    /**
     * @var int
     */
    private $iterations;

    /**
     * @var string Binary encoded string
     */
    private $salt;

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
     * @return int
     */
    public function getFlags(): int
    {
        return $this->flags;
    }

    /**
     * @param int $flags
     *
     * @throws \InvalidArgumentException
     */
    public function setFlags(int $flags): void
    {
        if (!Validator::isUnsignedInteger($flags, 8)) {
            throw new \InvalidArgumentException('Flags must be an 8-bit unsigned integer.');
        }
        $this->flags = $flags;
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
     * {@inheritdoc}
     */
    public function toText(): string
    {
        return sprintf('%d %d %d %s', $this->hashAlgorithm, $this->flags, $this->iterations, bin2hex($this->salt));
    }

    /**
     * {@inheritdoc}
     */
    public function toWire(): string
    {
        return pack('CCnC', $this->hashAlgorithm, $this->flags, $this->iterations, strlen($this->salt)).$this->salt;
    }

    /**
     * {@inheritdoc}
     *
     * @return NSEC3PARAM
     */
    public static function fromText(string $text): RdataInterface
    {
        $rdata = explode(Tokens::SPACE, $text);
        $nsec3param = new self();
        $nsec3param->setHashAlgorithm((int) array_shift($rdata));
        $nsec3param->setFlags((int) array_shift($rdata));
        $nsec3param->setIterations((int) array_shift($rdata));
        $nsec3param->setSalt((string) array_shift($rdata));

        return $nsec3param;
    }

    /**
     * {@inheritdoc}
     *
     * @return NSEC3PARAM
     */
    public static function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): RdataInterface
    {
        $integers = unpack('C<algorithm>/C<flags>/n<iterations>/C<saltLen>', $rdata, $offset);
        $saltLen = (int) $integers['<saltLen>'];
        $offset += 5;
        $nsec3param = new self();
        $nsec3param->setHashAlgorithm($integers['<algorithm>']);
        $nsec3param->setFlags($integers['<flags>']);
        $nsec3param->setIterations($integers['<iterations>']);

        $saltBin = substr($rdata, $offset, $saltLen);
        $nsec3param->setSalt(bin2hex($saltBin));
        $offset += $saltLen;

        return $nsec3param;
    }
}
