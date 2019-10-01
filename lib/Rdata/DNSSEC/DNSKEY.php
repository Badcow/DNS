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

/**
 * Class DNSKEY.
 *
 * {@link https://tools.ietf.org/html/rfc4034#section-2.1}
 */
class DNSKEY implements RdataInterface
{
    use RdataTrait;

    const TYPE = 'DNSKEY';

    /**
     * {@link https://tools.ietf.org/html/rfc4034#section-2.1.1}.
     *
     * @var int|null
     */
    private $flags;

    /**
     * The Protocol Field MUST have value 3, and the DNSKEY RR MUST be
     * treated as invalid during signature verification if it is found to be
     * some value other than 3.
     * {@link https://tools.ietf.org/html/rfc4034#section-2.1.2}.
     *
     * @var int
     */
    private $protocol = 3;

    /**
     * The Algorithm field identifies the public key's cryptographic
     * algorithm and determines the format of the Public Key field.
     * {@link https://tools.ietf.org/html/rfc4034#section-2.1.3}.
     *
     * @var int|null
     */
    private $algorithm;

    /**
     * The Public Key field is a Base64 encoding of the Public Key.
     * Whitespace is allowed within the Base64 text.
     * {@link https://tools.ietf.org/html/rfc4034#section-2.1.4}.
     *
     * @var string|null
     */
    private $publicKey;

    /**
     * @return int|null
     */
    public function getFlags(): ?int
    {
        return $this->flags;
    }

    /**
     * @param int $flags
     */
    public function setFlags(int $flags): void
    {
        $this->flags = $flags;
    }

    /**
     * @return int
     */
    public function getProtocol(): int
    {
        return $this->protocol;
    }

    /**
     * @return int|null
     */
    public function getAlgorithm(): ?int
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
     * @return string|null
     */
    public function getPublicKey(): ?string
    {
        return $this->publicKey;
    }

    /**
     * @param string $publicKey
     */
    public function setPublicKey(string $publicKey): void
    {
        $this->publicKey = $publicKey;
    }

    /**
     * {@inheritdoc}
     */
    public function output(): string
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
