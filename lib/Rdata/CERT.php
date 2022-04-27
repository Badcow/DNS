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

use Badcow\DNS\Algorithms;
use Badcow\DNS\Parser\Tokens;
use InvalidArgumentException;

/*
 * {@link https://tools.ietf.org/html/rfc4398#section-2.1}.
 */
class CERT implements RdataInterface
{
    use RdataTrait;

    public const TYPE = 'CERT';
    public const TYPE_CODE = 37;

    public const KEY_TYPE_PKIX = 1;
    public const KEY_TYPE_SPKI = 2;
    public const KEY_TYPE_PGP = 3;
    public const KEY_TYPE_IPKIX = 4;
    public const KEY_TYPE_ISPKI = 5;
    public const KEY_TYPE_IPGP = 6;
    public const KEY_TYPE_ACPKIX = 7;
    public const KEY_TYPE_IACPKIX = 8;
    public const KEY_TYPE_URI = 253;
    public const KEY_TYPE_OID = 254;

    public const MNEMONICS = [
        self::KEY_TYPE_PKIX => 'PKIX',
        self::KEY_TYPE_SPKI => 'SPKI',
        self::KEY_TYPE_PGP => 'PGP',
        self::KEY_TYPE_IPKIX => 'IPKIX',
        self::KEY_TYPE_ISPKI => 'ISPKI',
        self::KEY_TYPE_IPGP => 'IPGP',
        self::KEY_TYPE_ACPKIX => 'ACPKIX',
        self::KEY_TYPE_IACPKIX => 'IACPKIX',
        self::KEY_TYPE_URI => 'URI',
        self::KEY_TYPE_OID => 'OID',
    ];

    /**
     * @var int
     */
    private $certificateType;

    /**
     * @var int
     */
    private $keyTag;

    /**
     * @var int
     */
    private $algorithm;

    /**
     * @var string
     */
    private $certificate;

    public function getCertificateType(): int
    {
        return $this->certificateType;
    }

    /**
     * @param int|string $certificateType
     *
     * @throws InvalidArgumentException
     */
    public function setCertificateType($certificateType): void
    {
        if (is_int($certificateType) || 1 === preg_match('/^\d+$/', $certificateType)) {
            $this->certificateType = (int) $certificateType;

            return;
        }

        $this->certificateType = self::getKeyTypeValue((string) $certificateType);
    }

    public function getKeyTag(): int
    {
        return $this->keyTag;
    }

    public function setKeyTag(int $keyTag): void
    {
        $this->keyTag = $keyTag;
    }

    public function getAlgorithm(): int
    {
        return $this->algorithm;
    }

    /**
     * @param string|int $algorithm
     *
     * @throws InvalidArgumentException
     */
    public function setAlgorithm($algorithm): void
    {
        if (is_int($algorithm) || 1 === preg_match('/^\d+$/', $algorithm)) {
            $this->algorithm = (int) $algorithm;

            return;
        }

        $this->algorithm = Algorithms::getAlgorithmValue((string) $algorithm);
    }

    /**
     * @param string $certificate Base64 encoded string
     *
     * @throws InvalidArgumentException
     */
    public function setCertificate(string $certificate): void
    {
        $this->certificate = $certificate;
    }

    /**
     * @return string Base64 encoded string
     */
    public function getCertificate(): string
    {
        return $this->certificate;
    }

    public function toText(): string
    {
        $type = (array_key_exists($this->certificateType, self::MNEMONICS)) ? self::MNEMONICS[$this->certificateType] : (string) $this->certificateType;
        $algorithm = (array_key_exists($this->algorithm, Algorithms::MNEMONICS)) ? Algorithms::MNEMONICS[$this->algorithm] : (string) $this->algorithm;

        return sprintf('%s %s %s %s', $type, (string) $this->keyTag, $algorithm, base64_encode($this->certificate));
    }

    public function toWire(): string
    {
        return pack('nnC', $this->certificateType, $this->keyTag, $this->algorithm).$this->certificate;
    }

    public function fromText(string $text): void
    {
        $rdata = explode(Tokens::SPACE, $text);
        $this->setCertificateType((string) array_shift($rdata));
        $this->setKeyTag((int) array_shift($rdata));
        $this->setAlgorithm((string) array_shift($rdata));
        $this->setCertificate(base64_decode(implode('', $rdata)));
    }

    public function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): void
    {
        if (false === $integers = unpack('ntype/nkeyTag/Calgorithm', $rdata, $offset)) {
            throw new DecodeException(static::TYPE, $rdata);
        }
        $offset += 5;
        $this->setCertificateType((int) $integers['type']);
        $this->setKeyTag((int) $integers['keyTag']);
        $this->setAlgorithm((int) $integers['algorithm']);

        $certLen = ($rdLength ?? strlen($rdata)) - 5;
        $this->setCertificate(substr($rdata, $offset, $certLen));
        $offset += $certLen;
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function getKeyTypeValue(string $keyTypeMnemonic): int
    {
        if (false === $keyTypeValue = array_search($keyTypeMnemonic, self::MNEMONICS, true)) {
            throw new InvalidArgumentException(sprintf('"%s" is not a valid key type mnemonic.', $keyTypeMnemonic));
        }

        return (int) $keyTypeValue;
    }

    /**
     * Get the associated mnemonic of a key type.
     *
     * @throws InvalidArgumentException
     */
    public static function getKeyTypeMnemonic(int $keyTypeValue): string
    {
        if (!array_key_exists($keyTypeValue, self::MNEMONICS)) {
            throw new InvalidArgumentException(sprintf('"%d" is not a valid key type.', $keyTypeValue));
        }

        return self::MNEMONICS[$keyTypeValue];
    }
}
