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
     * @param int $algorithm
     */
    public function setAlgorithm(int $algorithm): void
    {
        $this->algorithm = $algorithm;
    }

    /**
     * @return int
     */
    public function getDigestType(): int
    {
        return $this->digestType;
    }

    /**
     * @param int $digestType
     */
    public function setDigestType(int $digestType): void
    {
        $this->digestType = $digestType;
    }

    /**
     * @return string
     */
    public function getDigest(): string
    {
        return $this->digest;
    }

    /**
     * @param string $digest
     */
    public function setDigest(string $digest): void
    {
        $this->digest = $digest;
    }

    /**
     * {@inheritdoc}
     */
    public function output(): string
    {
        return sprintf(
            '%s %s %s %s',
            $this->keyTag,
            $this->algorithm,
            $this->digestType,
            $this->digest
        );
    }
}
