<?php

/*
 * This file is part of Badcow DNS Library.
 *
 * (c) Samuel Williams <sam@badcow.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Badcow\DNS\Rdata\DNSSEC;

use Badcow\DNS\Rdata\RdataInterface;
use Badcow\DNS\Rdata\RdataTrait;

class RRSIG implements RdataInterface
{
    use RdataTrait;

    const TYPE = 'RRSIG';

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
     * @var
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
     * @var int
     */
    private $signatureExpiration;

    /**
     * 32-bit unsigned integer specifying the inception date of a signature.
     * {@link https://tools.ietf.org/html/rfc4034#section-3.1.5}.
     *
     * @var int
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
    public function getTypeCovered()
    {
        return $this->typeCovered;
    }

    /**
     * @param string $typeCovered
     */
    public function setTypeCovered($typeCovered)
    {
        $this->typeCovered = $typeCovered;
    }

    /**
     * @return int
     */
    public function getAlgorithm()
    {
        return $this->algorithm;
    }

    /**
     * @param int $algorithm
     */
    public function setAlgorithm($algorithm)
    {
        $this->algorithm = $algorithm;
    }

    /**
     * @return mixed
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * @param mixed $labels
     */
    public function setLabels($labels)
    {
        $this->labels = $labels;
    }

    /**
     * @return int
     */
    public function getOriginalTtl()
    {
        return $this->originalTtl;
    }

    /**
     * @param int $originalTtl
     */
    public function setOriginalTtl($originalTtl)
    {
        $this->originalTtl = $originalTtl;
    }

    /**
     * @return int
     */
    public function getSignatureExpiration()
    {
        return $this->signatureExpiration;
    }

    /**
     * @param int $signatureExpiration
     */
    public function setSignatureExpiration($signatureExpiration)
    {
        $this->signatureExpiration = $signatureExpiration;
    }

    /**
     * @return int
     */
    public function getSignatureInception()
    {
        return $this->signatureInception;
    }

    /**
     * @param int $signatureInception
     */
    public function setSignatureInception($signatureInception)
    {
        $this->signatureInception = $signatureInception;
    }

    /**
     * @return int
     */
    public function getKeyTag()
    {
        return $this->keyTag;
    }

    /**
     * @param int $keyTag
     */
    public function setKeyTag($keyTag)
    {
        $this->keyTag = $keyTag;
    }

    /**
     * @return string
     */
    public function getSignersName()
    {
        return $this->signersName;
    }

    /**
     * @param string $signersName
     */
    public function setSignersName($signersName)
    {
        $this->signersName = $signersName;
    }

    /**
     * @return string
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * @param string $signature
     */
    public function setSignature($signature)
    {
        $this->signature = $signature;
    }

    /**
     * {@inheritdoc}
     */
    public function output()
    {
        return sprintf(
            '%s %s %s %s %s %s %s %s %s',
            $this->typeCovered,
            $this->algorithm,
            $this->labels,
            $this->originalTtl,
            $this->signatureExpiration,
            $this->signatureInception,
            $this->keyTag,
            $this->signersName,
            $this->signature
        );
    }
}
