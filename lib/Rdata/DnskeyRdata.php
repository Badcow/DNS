<?php

/*
 * This file is part of Badcow DNS Library.
 *
 * (c) Samuel Williams <sam@badcow.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Badcow\DNS\Rdata;

/**
 * Class DnskeyRdata
 *
 * {@link https://tools.ietf.org/html/rfc4034#section-2.1}
 *
 * @package Badcow\DNS\Rdata
 */
class DnskeyRdata implements RdataInterface
{
    use RdataTrait;

    /**
     * RSA/MD5
     */
    const DNSSEC_RSAMD5 = 1;

    /**
     * Diffie-Hellman
     */
    const DNSSEC_DH = 2;

    /**
     * DSA/SHA-1
     */
    const DNSSEC_DSA = 3;

    /**
     * Elliptic Curve
     */
    const DNSSEC_ECC = 4;

    /**
     * RSA/SHA-1
     */
    const DNSSEC_RSASHA1 = 5;

    /**
     * Indirect
     */
    const DNSSEC_INDIRECT = 252;

    /**
     * Private
     */
    const DNSSEC_PRIVATEDNS = 253;

    /**
     * Private
     */
    const DNSSEC_PRIVATEOID = 254;

    const TYPE = 'DNSKEY';

    /**
     * @var int
     */
    private $flags;

    /**
     * @var int
     */
    private $protocol = 3;

    /**
     * @var int
     */
    private $algorithm;

    /**
     * The Public Key field is a Base64 encoding of the Public Key.
     * Whitespace is allowed within the Base64 text.
     *
     * @var string
     */
    private $publicKey;

    /**
     * @return int
     */
    public function getFlags()
    {
        return $this->flags;
    }

    /**
     * @param int $flags
     */
    public function setFlags($flags)
    {
        $this->flags = $flags;
    }

    /**
     * @return int
     */
    public function getProtocol()
    {
        return $this->protocol;
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
     * @return string
     */
    public function getPublicKey()
    {
        return $this->publicKey;
    }

    /**
     * @param string $publicKey
     */
    public function setPublicKey($publicKey)
    {
        $this->publicKey = $publicKey;
    }

    /**
     * {@inheritdoc}
     */
    public function output()
    {
        return sprintf(
            '%s %s %s %s',
            $this->flags,
            $this->protocol,
            $this->algorithm,
            $this->publicKey
        );
    }
}