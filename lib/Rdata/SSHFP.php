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
 * {@link https://tools.ietf.org/html/rfc4255}.
 */
class SSHFP implements RdataInterface
{
    use RdataTrait;

    const TYPE = 'SSHFP';
    const TYPE_CODE = 44;
    const ALGORITHM_RSA = 1;
    const ALGORITHM_DSA = 2;
    const FP_TYPE_SHA1 = 1;

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
        return bin2hex($this->fingerprint);
    }

    public function setFingerprint(string $fingerprint): void
    {
        if (!Validator::isBase16Encoded($fingerprint) || false === $fp = @hex2bin($fingerprint)) {
            throw new \InvalidArgumentException('The fingerprint MUST be a hexadecimal value.');
        }
        $this->fingerprint = $fp;
    }

    /**
     * {@inheritdoc}
     */
    public function toText(): string
    {
        return sprintf('%d %d %s', $this->algorithm, $this->fingerprintType, bin2hex($this->fingerprint));
    }

    /**
     * {@inheritdoc}
     */
    public function toWire(): string
    {
        return pack('CC', $this->algorithm, $this->fingerprintType).$this->fingerprint;
    }

    /**
     * {@inheritdoc}
     */
    public function fromText(string $text): void
    {
        $rdata = explode(Tokens::SPACE, $text);

        $this->setAlgorithm((int) array_shift($rdata));
        $this->setFingerprintType((int) array_shift($rdata));
        $this->setFingerprint((string) array_shift($rdata));
    }

    /**
     * {@inheritdoc}
     */
    public function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): void
    {
        $integers = unpack('C<algorithm>/C<fpType>', $rdata, $offset);
        $offset += 2;
        $this->setAlgorithm($integers['<algorithm>']);
        $this->setFingerprintType($integers['<fpType>']);
        $fpLen = ($rdLength ?? strlen($rdata)) - 2;
        $this->setFingerprint(bin2hex(substr($rdata, $offset, $fpLen)));
        $offset += $fpLen;
    }
}
