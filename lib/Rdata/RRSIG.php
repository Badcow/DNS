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

/**
 * {@link https://tools.ietf.org/html/rfc4034}.
 */
class RRSIG implements RdataInterface
{
    use RdataTrait;

    public const TYPE = 'RRSIG';
    public const TYPE_CODE = 46;
    public const TIME_FORMAT = 'YmdHis';

    /**
     *  The Type Covered field identifies the type of the RRset that is
     * covered by this RRSIG record. E.G. A, MX, AAAA, etc.
     *
     * @var string
     */
    private $typeCovered;

    /**
     * The Algorithm field identifies the public key's cryptographic
     * algorithm and determines the format of the Public Key field.
     * {@link https://tools.ietf.org/html/rfc4034#section-3.1.2}.
     *
     * @var int
     */
    private $algorithm;

    /**
     * The Labels field specifies the number of labels in the original RRSIG
     * RR owner name.
     * {@link https://tools.ietf.org/html/rfc4034#section-3.1.3}.
     *
     * @var int
     */
    private $labels;

    /**
     * The Original TTL field specifies the TTL of the covered RRset as it
     * appears in the authoritative zone.
     * {@link https://tools.ietf.org/html/rfc4034#section-3.1.4}.
     *
     * @var int
     */
    private $originalTtl;

    /**
     * 32-bit unsigned integer specifying the expiration date of a signature.
     * {@link https://tools.ietf.org/html/rfc4034#section-3.1.5}.
     *
     * @var \DateTime
     */
    private $signatureExpiration;

    /**
     * 32-bit unsigned integer specifying the inception date of a signature.
     * {@link https://tools.ietf.org/html/rfc4034#section-3.1.5}.
     *
     * @var \DateTime
     */
    private $signatureInception;

    /**
     * The Key Tag field contains the key tag value of the DNSKEY RR that
     * validates this signature, in network byte order.
     * {@link https://tools.ietf.org/html/rfc4034#section-3.1.6}.
     *
     * @var int
     */
    private $keyTag;

    /**
     * The Signer's Name field value identifies the owner name of the DNSKEY\
     * RR that a validator is supposed to use to validate this signature.
     * The Signer's Name field MUST contain the name of the zone of the
     * covered RRset.
     * {@link https://tools.ietf.org/html/rfc4034#section-3.1.7}.
     *
     * @var string
     */
    private $signersName;

    /**
     * The Signature field contains the cryptographic signature that covers
     * the RRSIG RDATA (excluding the Signature field) and the RRset
     * specified by the RRSIG owner name, RRSIG class, and RRSIG Type
     * Covered field.
     * {@link https://tools.ietf.org/html/rfc4034#section-3.1.8}.
     *
     * @var string
     */
    private $signature;

    public function getTypeCovered(): string
    {
        return $this->typeCovered;
    }

    public function setTypeCovered(string $typeCovered): void
    {
        $this->typeCovered = $typeCovered;
    }

    public function getAlgorithm(): int
    {
        return $this->algorithm;
    }

    public function setAlgorithm(int $algorithm): void
    {
        $this->algorithm = $algorithm;
    }

    public function getLabels(): int
    {
        return $this->labels;
    }

    public function setLabels(int $labels): void
    {
        $this->labels = $labels;
    }

    public function getOriginalTtl(): int
    {
        return $this->originalTtl;
    }

    public function setOriginalTtl(int $originalTtl): void
    {
        $this->originalTtl = $originalTtl;
    }

    public function getSignatureExpiration(): \DateTime
    {
        return $this->signatureExpiration;
    }

    public function setSignatureExpiration(\DateTime $signatureExpiration): void
    {
        $this->signatureExpiration = $signatureExpiration;
    }

    public function getSignatureInception(): \DateTime
    {
        return $this->signatureInception;
    }

    public function setSignatureInception(\DateTime $signatureInception): void
    {
        $this->signatureInception = $signatureInception;
    }

    public function getKeyTag(): int
    {
        return $this->keyTag;
    }

    public function setKeyTag(int $keyTag): void
    {
        $this->keyTag = $keyTag;
    }

    public function getSignersName(): string
    {
        return $this->signersName;
    }

    public function setSignersName(string $signersName): void
    {
        $this->signersName = $signersName;
    }

    public function getSignature(): string
    {
        return $this->signature;
    }

    public function setSignature(string $signature): void
    {
        $this->signature = $signature;
    }

    public function toText(): string
    {
        return sprintf(
            '%s %s %s %s %s %s %s %s %s',
            $this->typeCovered,
            $this->algorithm,
            $this->labels,
            $this->originalTtl,
            $this->signatureExpiration->format(self::TIME_FORMAT),
            $this->signatureInception->format(self::TIME_FORMAT),
            $this->keyTag,
            $this->signersName,
            base64_encode($this->signature)
        );
    }

    /**
     * @throws UnsupportedTypeException
     */
    public function toWire(): string
    {
        $wire = pack(
            'nCCNNNn',
            Types::getTypeCode($this->typeCovered),
            $this->algorithm,
            $this->labels,
            $this->originalTtl,
            (int) $this->signatureExpiration->format('U'),
            (int) $this->signatureInception->format('U'),
            $this->keyTag
        );

        $wire .= Message::encodeName($this->signersName);
        $wire .= $this->signature;

        return $wire;
    }

    public function fromText(string $text): void
    {
        $rdata = explode(Tokens::SPACE, $text);

        $this->setTypeCovered((string) array_shift($rdata));
        $this->setAlgorithm((int) array_shift($rdata));
        $this->setLabels((int) array_shift($rdata));
        $this->setOriginalTtl((int) array_shift($rdata));
        $this->setSignatureExpiration(self::makeDateTime((string) array_shift($rdata)));
        $this->setSignatureInception(self::makeDateTime((string) array_shift($rdata)));
        $this->setKeyTag((int) array_shift($rdata));
        $this->setSignersName((string) array_shift($rdata));
        $this->setSignature(base64_decode(implode('', $rdata)));
    }

    /**
     * @throws UnsupportedTypeException
     */
    public function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): void
    {
        $rdLength = $rdLength ?? strlen($rdata);
        $end = $offset + $rdLength;
        if (false === $values = unpack('n<type>/C<algorithm>/C<labels>/N<originalTtl>/N<sigExpiration>/N<sigInception>/n<keyTag>', $rdata, $offset)) {
            throw new DecodeException(static::TYPE, $rdata);
        }
        $offset += 18;
        $signersName = Message::decodeName($rdata, $offset);

        $sigLen = $end - $offset;
        $signature = substr($rdata, $offset, $sigLen);
        $offset += $sigLen;

        $this->setTypeCovered(Types::getName($values['<type>']));
        $this->setAlgorithm($values['<algorithm>']);
        $this->setLabels($values['<labels>']);
        $this->setOriginalTtl($values['<originalTtl>']);
        $this->setKeyTag($values['<keyTag>']);
        $this->setSignersName($signersName);
        $this->setSignature($signature);

        $this->setSignatureExpiration(self::makeDateTime((string) $values['<sigExpiration>']));
        $this->setSignatureInception(self::makeDateTime((string) $values['<sigInception>']));
    }

    private static function makeDateTime(string $timeString): \DateTime
    {
        $timeFormat = (14 === strlen($timeString)) ? self::TIME_FORMAT : 'U';
        if (false === $dateTime = \DateTime::createFromFormat($timeFormat, $timeString)) {
            throw new \InvalidArgumentException(sprintf('Unable to create \DateTime object from date "%s".', $timeString));
        }

        return $dateTime;
    }
}
