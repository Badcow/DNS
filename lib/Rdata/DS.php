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
use InvalidArgumentException;

class DS implements RdataInterface
{
    use RdataTrait;

    public const TYPE = 'DS';
    public const TYPE_CODE = 43;
    public const DIGEST_SHA1 = 1;

    /**
     * @var int
     */
    private $keyTag;

    /**
     * The Algorithm field lists the algorithm number of the DNSKEY RR
     * referred to by the DS record.
     * {@link https://tools.ietf.org/html/rfc4034#section-5.1.2}.
     *
     * @var int
     */
    private $algorithm;

    /**
     * @var int
     */
    private $digestType = self::DIGEST_SHA1;

    /**
     * @var string
     */
    private $digest;

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

    public function setAlgorithm(int $algorithm): void
    {
        $this->algorithm = $algorithm;
    }

    public function getDigestType(): int
    {
        return $this->digestType;
    }

    public function setDigestType(int $digestType): void
    {
        $this->digestType = $digestType;
    }

    /**
     * @return string the digest in its binary representation
     */
    public function getDigest(): string
    {
        return $this->digest;
    }

    /**
     * @param string $digest the digest in its binary representation
     */
    public function setDigest(string $digest): void
    {
        $this->digest = $digest;
    }

    /**
     * Calculates the digest by concatenating the canonical form of the fully qualified owner name of the DNSKEY RR with
     * the DNSKEY RDATA, and then applying the digest algorithm.
     *
     * @param string $owner  canonical form of the fully qualified owner name of the DNSKEY RR
     * @param DNSKEY $dnskey Owner's DNSKEY
     */
    public function calculateDigest(string $owner, DNSKEY $dnskey): void
    {
        if (static::DIGEST_SHA1 !== $this->digestType) {
            throw new InvalidArgumentException('Can only calculate SHA-1 digests.');
        }

        $this->digest = sha1(Message::encodeName(strtolower($owner)).$dnskey->toWire(), true);
    }

    public function toText(): string
    {
        return sprintf(
            '%s %s %s %s',
            $this->keyTag,
            $this->algorithm,
            $this->digestType,
            strtoupper(bin2hex($this->digest))
        );
    }

    public function toWire(): string
    {
        return pack('nCC', $this->keyTag, $this->algorithm, $this->digestType).$this->digest;
    }

    public function fromText(string $text): void
    {
        $rdata = explode(Tokens::SPACE, $text);
        $this->setKeyTag((int) array_shift($rdata));
        $this->setAlgorithm((int) array_shift($rdata));
        $this->setDigestType((int) array_shift($rdata));
        if (false === $digest = hex2bin((string) array_shift($rdata))) {
            throw new InvalidArgumentException(sprintf('The digest is not a valid hexadecimal string.'));
        }
        $this->setDigest($digest);
    }

    public function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): void
    {
        $digestLen = ($rdLength ?? strlen($rdata)) - 4;

        if (false === $integers = unpack('ntag/Calgorithm/Cdtype', $rdata, $offset)) {
            throw new DecodeException(static::TYPE, $rdata);
        }
        $offset += 4;

        $this->setKeyTag($integers['tag']);
        $this->setAlgorithm($integers['algorithm']);
        $this->setDigestType($integers['dtype']);
        $this->setDigest(substr($rdata, $offset, $digestLen));

        $offset += $digestLen;
    }
}
