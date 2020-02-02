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

    /**
     * @return int
     */
    public function getAlgorithm(): int
    {
        return $this->algorithm;
    }

    /**
     * @param int $algorithm
     *
     * @throws \InvalidArgumentException
     */
    public function setAlgorithm(int $algorithm): void
    {
        if (!Validator::isUnsignedInteger($algorithm, 8)) {
            throw new \InvalidArgumentException('Algorithm must be an 8-bit integer between 0 and 255.');
        }
        $this->algorithm = $algorithm;
    }

    /**
     * @return int
     */
    public function getFingerprintType(): int
    {
        return $this->fingerprintType;
    }

    /**
     * @param int $fingerprintType
     *
     * @throws \InvalidArgumentException
     */
    public function setFingerprintType(int $fingerprintType): void
    {
        if (!Validator::isUnsignedInteger($fingerprintType, 8)) {
            throw new \InvalidArgumentException('Fingerprint type must be an 8-bit integer between 0 and 255.');
        }
        $this->fingerprintType = $fingerprintType;
    }

    /**
     * @return string
     */
    public function getFingerprint(): string
    {
        return bin2hex($this->fingerprint);
    }

    /**
     * @param string $fingerprint
     */
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
     *
     * @return SSHFP
     */
    public static function fromText(string $text): RdataInterface
    {
        $rdata = explode(Tokens::SPACE, $text);

        return Factory::SSHFP((int) array_shift($rdata), (int) array_shift($rdata), (string) array_shift($rdata));
    }

    /**
     * {@inheritdoc}
     *
     * @return SSHFP
     */
    public static function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): RdataInterface
    {
        $integers = unpack('C<algorithm>/C<fpType>', $rdata, $offset);
        $offset += 2;
        $sshfp = new self();
        $sshfp->setAlgorithm($integers['<algorithm>']);
        $sshfp->setFingerprintType($integers['<fpType>']);
        $fpLen = ($rdLength ?? strlen($rdata)) - 2;
        $sshfp->setFingerprint(bin2hex(substr($rdata, $offset, $fpLen)));
        $offset += $fpLen;

        return $sshfp;
    }
}
