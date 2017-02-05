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

class DsRdata implements RdataInterface
{
    use RdataTrait;

    const TYPE = 'DS';

    const DIGEST_SHA1 = 1;

    /**
     * @var int
     */
    private $keyTag;

    /**
     * The Algorithm field lists the algorithm number of the DNSKEY RR
     * referred to by the DS record.
     * {@link https://tools.ietf.org/html/rfc4034#section-5.1.2}
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
    public function getKeyTag()
    {
        return $this->keyTag;
    }

    /**
     * @param int $keyTag
     */
    public function setKeyTag($keyTag)
    {
        $this->keyTag = (int) $keyTag;
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
        $this->algorithm = (int) $algorithm;
    }

    /**
     * @return int
     */
    public function getDigestType()
    {
        return $this->digestType;
    }

    /**
     * @param int $digestType
     */
    public function setDigestType($digestType)
    {
        $this->digestType = (int) $digestType;
    }

    /**
     * @return string
     */
    public function getDigest()
    {
        return $this->digest;
    }

    /**
     * @param string $digest
     */
    public function setDigest($digest)
    {
        $this->digest = $digest;
    }

    /**
     * {@inheritdoc}
     */
    public function output()
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