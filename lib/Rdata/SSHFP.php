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
use Badcow\DNS\Validator;

/**
 * {@link https://tools.ietf.org/html/rfc4255}.
 */
class SSHFP implements RdataInterface
{
    use RdataTrait;

    public const TYPE = 'SSHFP';
    public const TYPE_CODE = 44;
    public const ALGORITHM_RSA = 1;
    public const ALGORITHM_DSA = 2;
    public const FP_TYPE_SHA1 = 1;

    /**
     * 8-bit algorithm designate.
     *
     * @var int
     */
    private $algorithm;

    /**
     * 8-bit Fingerprint type.
     *
     * @var int
     */
    private $fingerprintType = self::FP_TYPE_SHA1;

    /**
     * Hexadecimal string.
     *
     * @var string
     */
    private $fingerprint;

    public function getAlgorithm(): int
    {
        return $this->algorithm;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function setAlgorithm(int $algorithm): void
    {
        if (!Validator::isUnsignedInteger($algorithm, 8)) {
            throw new \InvalidArgumentException('Algorithm must be an 8-bit integer between 0 and 255.');
        }
        $this->algorithm = $algorithm;
    }

    public function getFingerprintType(): int
    {
        return $this->fingerprintType;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function setFingerprintType(int $fingerprintType): void
    {
        if (!Validator::isUnsignedInteger($fingerprintType, 8)) {
            throw new \InvalidArgumentException('Fingerprint type must be an 8-bit integer between 0 and 255.');
        }
        $this->fingerprintType = $fingerprintType;
    }

    public function getFingerprint(): string
    {
        return $this->fingerprint;
    }

    public function setFingerprint(string $fingerprint): void
    {
        $this->fingerprint = $fingerprint;
    }

    public function toText(): string
    {
        return sprintf('%d %d %s', $this->algorithm, $this->fingerprintType, bin2hex($this->fingerprint));
    }

    public function toWire(): string
    {
        return pack('CC', $this->algorithm, $this->fingerprintType).$this->fingerprint;
    }

    /**
     * @throws ParseException
     */
    public function fromText(string $text): void
    {
        $rdata = explode(Tokens::SPACE, $text);

        $this->setAlgorithm((int) array_shift($rdata));
        $this->setFingerprintType((int) array_shift($rdata));
        if (false === $fingerprint = hex2bin((string) array_shift($rdata))) {
            throw new ParseException('Fingerprint could no be parsed. Invalid hexadecimal.');
        }
        $this->setFingerprint($fingerprint);
    }

    public function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): void
    {
        if (false === $integers = unpack('C<algorithm>/C<fpType>', $rdata, $offset)) {
            throw new DecodeException(static::TYPE, $rdata);
        }
        $offset += 2;
        $this->setAlgorithm($integers['<algorithm>']);
        $this->setFingerprintType($integers['<fpType>']);
        $fpLen = ($rdLength ?? strlen($rdata)) - 2;
        $this->setFingerprint(substr($rdata, $offset, $fpLen));
        $offset += $fpLen;
    }
}
