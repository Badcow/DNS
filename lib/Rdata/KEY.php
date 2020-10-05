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
 * {@link https://tools.ietf.org/html/rfc2535#section-3.1}.
 */
class KEY implements RdataInterface
{
    use RdataTrait;

    const TYPE = 'KEY';
    const TYPE_CODE = 25;

    /**
     * {@link https://tools.ietf.org/html/rfc4034#section-2.1.1}.
     *
     * @var int
     */
    protected $flags;

    /**
     * @var int
     */
    protected $protocol;

    /**
     * The Algorithm field identifies the public key's cryptographic
     * algorithm and determines the format of the Public Key field.
     * {@link https://tools.ietf.org/html/rfc4034#section-2.1.3}.
     *
     * @var int
     */
    protected $algorithm;

    /**
     * The Public Key field is a Base64 encoding of the Public Key.
     * Whitespace is allowed within the Base64 text.
     * {@link https://tools.ietf.org/html/rfc4034#section-2.1.4}.
     *
     * @var string
     */
    protected $publicKey;

    public function setFlags(int $flags): void
    {
        $this->flags = $flags;
    }

    public function setProtocol(int $protocol): void
    {
        $this->protocol = $protocol;
    }

    public function setAlgorithm(int $algorithm): void
    {
        $this->algorithm = $algorithm;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function setPublicKey(string $publicKey): void
    {
        if (false === $key = base64_decode($publicKey, true)) {
            throw new \InvalidArgumentException('The public key must be a valid base64 encoded string.');
        }

        $this->publicKey = $key;
    }

    public function getFlags(): int
    {
        return $this->flags;
    }

    public function getProtocol(): int
    {
        return $this->protocol;
    }

    public function getAlgorithm(): int
    {
        return $this->algorithm;
    }

    public function getPublicKey(): string
    {
        return base64_encode($this->publicKey);
    }

    /**
     * {@inheritdoc}
     */
    public function toText(): string
    {
        return sprintf('%d %d %d %s', $this->flags, $this->protocol, $this->algorithm, base64_encode($this->publicKey));
    }

    /**
     * {@inheritdoc}
     */
    public function toWire(): string
    {
        return pack('nCC', $this->flags, $this->protocol, $this->algorithm).$this->publicKey;
    }

    /**
     * {@inheritdoc}
     */
    public function fromText(string $text): void
    {
        $rdata = explode(Tokens::SPACE, $text);
        $this->setFlags((int) array_shift($rdata));
        $this->setProtocol((int) array_shift($rdata));
        $this->setAlgorithm((int) array_shift($rdata));
        $this->setPublicKey(implode('', $rdata));
    }

    /**
     * {@inheritdoc}
     */
    public function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): void
    {
        $rdLength = $rdLength ?? strlen($rdata);
        $integers = unpack('nflags/Cprotocol/Calgorithm', $rdata, $offset);
        $offset += 4;
        $this->setFlags((int) $integers['flags']);
        $this->setProtocol((int) $integers['protocol']);
        $this->setAlgorithm((int) $integers['algorithm']);
        $this->publicKey = substr($rdata, $offset, $rdLength - 4);
        $offset += $rdLength - 4;
    }
}
