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

class DS implements RdataInterface
{
    use RdataTrait;

    const TYPE = 'DS';
    const TYPE_CODE = 43;
    const DIGEST_SHA1 = 1;

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

    public function getDigest(): string
    {
        return $this->digest;
    }

    public function setDigest(string $digest): void
    {
        $this->digest = $digest;
    }

    /**
     * {@inheritdoc}
     */
    public function toText(): string
    {
        return sprintf(
            '%s %s %s %s',
            $this->keyTag,
            $this->algorithm,
            $this->digestType,
            $this->digest
        );
    }

    /**
     * {@inheritdoc}
     */
    public function toWire(): string
    {
        return pack('nCC', $this->keyTag, $this->algorithm, $this->digestType).$this->digest;
    }

    /**
     * {@inheritdoc}
     */
    public function fromText(string $text): void
    {
        $rdata = explode(Tokens::SPACE, $text);
        $this->setKeyTag((int) array_shift($rdata));
        $this->setAlgorithm((int) array_shift($rdata));
        $this->setDigestType((int) array_shift($rdata));
        $this->setDigest((string) array_shift($rdata));
    }

    /**
     * {@inheritdoc}
     */
    public function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): void
    {
        $integers = unpack('ntag/Calgorithm/Cdtype', $rdata);
        $offset += 4;
        $this->setKeyTag($integers['tag']);
        $this->setAlgorithm($integers['algorithm']);
        $this->setDigestType($integers['dtype']);

        $digestLen = ($rdLength ?? strlen($rdata)) - 4;
        $this->setDigest(substr($rdata, $offset, $digestLen));
        $offset += $digestLen;
    }
}
