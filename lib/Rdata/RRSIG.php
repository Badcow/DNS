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

/**
 * {@link https://tools.ietf.org/html/rfc4034}.
 */
class RRSIG implements RdataInterface
{
    use RdataTrait;

    const TYPE = 'RRSIG';
    const TYPE_CODE = 46;
    const TIME_FORMAT = 'YmdHis';

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

    /**
     * @return string
     */
    public function getTypeCovered(): string
    {
        return $this->typeCovered;
    }

    /**
     * @param string $typeCovered
     */
    public function setTypeCovered(string $typeCovered): void
    {
        $this->typeCovered = $typeCovered;
    }

    /**
     * @return int
     */
    public function getAlgorithm(): int
    {
        return $this->algorithm;
    }

    /**
     * @param int $algorithm
     */
    public function setAlgorithm(int $algorithm): void
    {
        $this->algorithm = $algorithm;
    }

    /**
     * @return int
     */
    public function getLabels(): int
    {
        return $this->labels;
    }

    /**
     * @param int $labels
     */
    public function setLabels(int $labels): void
    {
        $this->labels = $labels;
    }

    /**
     * @return int
     */
    public function getOriginalTtl(): int
    {
        return $this->originalTtl;
    }

    /**
     * @param int $originalTtl
     */
    public function setOriginalTtl(int $originalTtl): void
    {
        $this->originalTtl = $originalTtl;
    }

    /**
     * @return \DateTime
     */
    public function getSignatureExpiration(): \DateTime
    {
        return $this->signatureExpiration;
    }

    /**
     * @param \DateTime $signatureExpiration
     */
    public function setSignatureExpiration(\DateTime $signatureExpiration): void
    {
        $this->signatureExpiration = $signatureExpiration;
    }

    /**
     * @return \DateTime
     */
    public function getSignatureInception(): \DateTime
    {
        return $this->signatureInception;
    }

    /**
     * @param \DateTime $signatureInception
     */
    public function setSignatureInception(\DateTime $signatureInception): void
    {
        $this->signatureInception = $signatureInception;
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
     * @return string
     */
    public function getSignersName(): string
    {
        return $this->signersName;
    }

    /**
     * @param string $signersName
     */
    public function setSignersName(string $signersName): void
    {
        $this->signersName = $signersName;
    }

    /**
     * @return string
     */
    public function getSignature(): string
    {
        return $this->signature;
    }

    /**
     * @param string $signature
     */
    public function setSignature(string $signature): void
    {
        $this->signature = $signature;
    }

    /**
     * {@inheritdoc}
     */
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
            $this->signature
        );
    }

    /**
     * @return string
     *
     * @throws UnsupportedTypeException
     */
    public function toWire(): string
    {
        $wire = pack('nCCNNNn',
            Types::getTypeCode($this->typeCovered),
            $this->algorithm,
            $this->labels,
            $this->originalTtl,
            (int) $this->signatureExpiration->format('U'),
            (int) $this->signatureInception->format('U'),
            $this->keyTag
        );

        $wire .= self::encodeName($this->signersName);
        $wire .= $this->signature;

        return $wire;
    }

    /**
     * {@inheritdoc}
     *
     * @return RRSIG
     */
    public static function fromText(string $text): RdataInterface
    {
        $rdata = explode(Tokens::SPACE, $text);
        $rrsig = new static();
        $rrsig->setTypeCovered((string) array_shift($rdata));
        $rrsig->setAlgorithm((int) array_shift($rdata));
        $rrsig->setLabels((int) array_shift($rdata));
        $rrsig->setOriginalTtl((int) array_shift($rdata));
        $sigExpiration = (string) array_shift($rdata);
        $sigInception = (string) array_shift($rdata);
        $rrsig->setKeyTag((int) array_shift($rdata));
        $rrsig->setSignersName((string) array_shift($rdata));
        $rrsig->setSignature(implode('', $rdata));

        $rrsig->setSignatureExpiration(self::makeDateTime($sigExpiration));
        $rrsig->setSignatureInception(self::makeDateTime($sigInception));

        return $rrsig;
    }

    /**
     * {@inheritdoc}
     *
     * @return RRSIG
     *
     * @throws UnsupportedTypeException
     */
    public static function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): RdataInterface
    {
        $rdLength = $rdLength ?? strlen($rdata);
        $end = $offset + $rdLength;
        $values = unpack('n<type>/C<algorithm>/C<labels>/N<originalTtl>/N<sigExpiration>/N<sigInception>/n<keyTag>', $rdata, $offset);
        $offset += 18;
        $signersName = self::decodeName($rdata, $offset);

        $sigLen = $end - $offset;
        $signature = substr($rdata, $offset, $sigLen);
        $offset += $sigLen;

        $rrsig = new static();
        $rrsig->setTypeCovered(Types::getName($values['<type>']));
        $rrsig->setAlgorithm($values['<algorithm>']);
        $rrsig->setLabels($values['<labels>']);
        $rrsig->setOriginalTtl($values['<originalTtl>']);
        $rrsig->setKeyTag($values['<keyTag>']);
        $rrsig->setSignersName($signersName);
        $rrsig->setSignature($signature);

        $rrsig->setSignatureExpiration(self::makeDateTime((string) $values['<sigExpiration>']));
        $rrsig->setSignatureInception(self::makeDateTime((string) $values['<sigInception>']));

        return $rrsig;
    }

    /**
     * @param string $timeString
     *
     * @return \DateTime
     */
    private static function makeDateTime(string $timeString): \DateTime
    {
        $timeFormat = (14 === strlen($timeString)) ? self::TIME_FORMAT : 'U';
        if (false === $dateTime = \DateTime::createFromFormat($timeFormat, $timeString)) {
            throw new \InvalidArgumentException(sprintf('Unable to create \DateTime object from date "%s".', $timeString));
        }

        return $dateTime;
    }
}
