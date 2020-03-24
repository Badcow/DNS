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

use Badcow\DNS\Algorithms as _Algorithms;
use Badcow\DNS\Parser\Tokens;

/*
 * {@link https://tools.ietf.org/html/rfc4398#section-2.1}.
 */
class CERT implements RdataInterface
{
    use RdataTrait;

    const TYPE = 'CERT';
    const TYPE_CODE = 37;

    const KEY_TYPE_PKIX = 1;
    const KEY_TYPE_SPKI = 2;
    const KEY_TYPE_PGP = 3;
    const KEY_TYPE_IPKIX = 4;
    const KEY_TYPE_ISPKI = 5;
    const KEY_TYPE_IPGP = 6;
    const KEY_TYPE_ACPKIX = 7;
    const KEY_TYPE_IACPKIX = 8;
    const KEY_TYPE_URI = 253;
    const KEY_TYPE_OID = 254;

    const MNEMONICS = [
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

    /**
     * @return int
     */
    public function getCertificateType(): int
    {
        return $this->certificateType;
    }

    /**
     * @param int|string $certificateType
     *
     * @throws \InvalidArgumentException
     */
    public function setCertificateType($certificateType): void
    {
        if (is_int($certificateType) || 1 === preg_match('/^\d+$/', $certificateType)) {
            $this->certificateType = (int) $certificateType;

            return;
        }

        $this->certificateType = self::getKeyTypeValue((string) $certificateType);
    }

    /**
     * @return int
     */
    public function getKeyTag(): int
    {
        return $this->keyTag;
    }

    /**
     * @param int $keyTag
     */
    public function setKeyTag(int $keyTag): void
    {
        $this->keyTag = $keyTag;
    }

    /**
     * @return int
     */
    public function getAlgorithm(): int
    {
        return $this->algorithm;
    }

    /**
     * @param string|int $algorithm
     *
     * @throws \InvalidArgumentException
     */
    public function setAlgorithm($algorithm): void
    {
        if (is_int($algorithm) || 1 === preg_match('/^\d+$/', $algorithm)) {
            $this->algorithm = (int) $algorithm;

            return;
        }

        $this->algorithm = _Algorithms::getAlgorithmValue((string) $algorithm);
    }

    /**
     * @param string $certificate Base64 encoded string
     *
     * @throws \InvalidArgumentException
     */
    public function setCertificate(string $certificate): void
    {
        if (false === $decoded = base64_decode($certificate, true)) {
            throw new \InvalidArgumentException('The certificate must be a valid Base64 encoded string.');
        }

        $this->certificate = $decoded;
    }

    /**
     * @return string Base64 encoded string
     */
    public function getCertificate(): string
    {
        return base64_encode($this->certificate);
    }

    /**
     * {@inheritdoc}
     */
    public function toText(): string
    {
        $type = (array_key_exists($this->certificateType, self::MNEMONICS)) ? self::MNEMONICS[$this->certificateType] : (string) $this->certificateType;
        $algorithm = (array_key_exists($this->algorithm, _Algorithms::MNEMONICS)) ? _Algorithms::MNEMONICS[$this->algorithm] : (string) $this->algorithm;

        return sprintf('%s %s %s %s', $type, (string) $this->keyTag, $algorithm, $this->getCertificate());
    }

    /**
     * {@inheritdoc}
     */
    public function toWire(): string
    {
        return pack('nnC', $this->certificateType, $this->keyTag, $this->algorithm).$this->certificate;
    }

    /**
     * {@inheritdoc}
     */
    public static function fromText(string $text): RdataInterface
    {
        $rdata = explode(Tokens::SPACE, $text);
        $cert = new static();
        $cert->setCertificateType((string) array_shift($rdata));
        $cert->setKeyTag((int) array_shift($rdata));
        $cert->setAlgorithm((string) array_shift($rdata));
        $cert->setCertificate(implode('', $rdata));

        return $cert;
    }

    /**
     * {@inheritdoc}
     */
    public static function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): RdataInterface
    {
        $integers = unpack('ntype/nkeyTag/Calgorithm', $rdata, $offset);
        $offset += 5;
        $cert = new static();
        $cert->setCertificateType((int) $integers['type']);
        $cert->setKeyTag((int) $integers['keyTag']);
        $cert->setAlgorithm((int) $integers['algorithm']);

        $certLen = ($rdLength ?? strlen($rdata)) - 5;
        $cert->setCertificate(base64_encode(substr($rdata, $offset, $certLen)));
        $offset += $certLen;

        return $cert;
    }

    /**
     * @param string $keyTypeMnemonic
     *
     * @return int
     *
     * @throws \InvalidArgumentException
     */
    public static function getKeyTypeValue(string $keyTypeMnemonic): int
    {
        if (false === $keyTypeValue = array_search($keyTypeMnemonic, self::MNEMONICS, true)) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid key type mnemonic.', $keyTypeMnemonic));
        }

        return (int) $keyTypeValue;
    }

    /**
     * Get the associated mnemonic of a key type.
     *
     * @param int $keyTypeValue
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public static function getKeyTypeMnemonic(int $keyTypeValue): string
    {
        if (!array_key_exists($keyTypeValue, self::MNEMONICS)) {
            throw new \InvalidArgumentException(sprintf('"%d" is not a valid key type.', $keyTypeValue));
        }

        return self::MNEMONICS[$keyTypeValue];
    }
}
