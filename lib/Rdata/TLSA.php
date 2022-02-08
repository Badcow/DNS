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
 * {@link https://tools.ietf.org/html/rfc6698}.
 */
class TLSA implements RdataInterface
{
    use RdataTrait;

    public const TYPE = 'TLSA';
    public const TYPE_CODE = 52;

    /**
     * A one-octet value, called "certificate usage", specifies the provided
     * association that will be used to match the certificate presented in
     * the TLS handshake.
     *
     * @var int uint8
     */
    private $certificateUsage;

    /**
     * A one-octet value, called "selector", specifies which part of the TLS
     * certificate presented by the server will be matched against the
     * association data.
     *
     * @var int uint8
     */
    private $selector;

    /**
     * A one-octet value, called "matching type", specifies how the
     * certificate association is presented.
     *
     * @var int uint8
     */
    private $matchingType;

    /**
     * This field specifies the "certificate association data" to be
     * matched.  These bytes are either raw data (that is, the full
     * certificate or its SubjectPublicKeyInfo, depending on the selector)
     * for matching type 0, or the hash of the raw data for matching types 1
     * and 2.  The data refers to the certificate in the association, not to
     * the TLS ASN.1 Certificate object.
     *
     * @var string
     */
    private $certificateAssociationData;

    public function getCertificateUsage(): int
    {
        return $this->certificateUsage;
    }

    public function setCertificateUsage(int $certificateUsage): void
    {
        if (!Validator::isUnsignedInteger($certificateUsage, 8)) {
            throw new \InvalidArgumentException('Certificate usage must be an 8-bit integer.');
        }
        $this->certificateUsage = $certificateUsage;
    }

    public function getSelector(): int
    {
        return $this->selector;
    }

    public function setSelector(int $selector): void
    {
        if (!Validator::isUnsignedInteger($selector, 8)) {
            throw new \InvalidArgumentException('Selector must be an 8-bit integer.');
        }
        $this->selector = $selector;
    }

    public function getMatchingType(): int
    {
        return $this->matchingType;
    }

    public function setMatchingType(int $matchingType): void
    {
        if (!Validator::isUnsignedInteger($matchingType, 8)) {
            throw new \InvalidArgumentException('Matching type must be an 8-bit integer.');
        }
        $this->matchingType = $matchingType;
    }

    public function getCertificateAssociationData(): string
    {
        return $this->certificateAssociationData;
    }

    public function setCertificateAssociationData(string $certificateAssociationData): void
    {
        $this->certificateAssociationData = $certificateAssociationData;
    }

    public function toText(): string
    {
        return sprintf('%d %d %d %s', $this->certificateUsage, $this->selector, $this->matchingType, bin2hex($this->certificateAssociationData));
    }

    public function toWire(): string
    {
        return pack('CCC', $this->certificateUsage, $this->selector, $this->matchingType).$this->certificateAssociationData;
    }

    /**
     * @throws ParseException
     */
    public function fromText(string $text): void
    {
        $rdata = explode(Tokens::SPACE, $text);
        $this->setCertificateUsage((int) array_shift($rdata));
        $this->setSelector((int) array_shift($rdata));
        $this->setMatchingType((int) array_shift($rdata));
        if (false === $certificateAssociationData = @hex2bin(implode('', $rdata))) {
            throw new ParseException('Unable to parse certificate association data of TLSA record. Malformed hex value.');
        }
        $this->setCertificateAssociationData($certificateAssociationData);
    }

    public function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): void
    {
        if (false === $integers = unpack('C<certUsage>/C<selector>/C<matchingType>', $rdata, $offset)) {
            throw new DecodeException(static::TYPE, $rdata);
        }
        $offset += 3;
        $this->setCertificateUsage($integers['<certUsage>']);
        $this->setSelector($integers['<selector>']);
        $this->setMatchingType($integers['<matchingType>']);
        $cadLen = ($rdLength ?? strlen($rdata)) - 3;
        $this->setCertificateAssociationData(substr($rdata, $offset, $cadLen));
        $offset += $cadLen;
    }
}
