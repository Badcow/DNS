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
 * @see https://tools.ietf.org/html/rfc1035#section-3.3.13
 */
class SOA implements RdataInterface
{
    use RdataTrait;

    const TYPE = 'SOA';
    const TYPE_CODE = 6;

    /**
     * The <domain-name> of the name server that was the
     * original or primary source of data for this zone.
     *
     * @var string|null
     */
    private $mname;

    /**
     * A <domain-name> which specifies the mailbox of the
     * person responsible for this zone.
     *
     * @var string|null
     */
    private $rname;

    /**
     * The unsigned 32 bit version number of the original copy
     * of the zone.
     *
     * @var int|null
     */
    private $serial;

    /**
     * A 32 bit time interval before the zone should be
     * refreshed.
     *
     * @var int|null
     */
    private $refresh;

    /**
     * A 32 bit time interval that should elapse before a
     * failed refresh should be retried.
     *
     * @var int|null
     */
    private $retry;

    /**
     * A 32 bit time value that specifies the upper limit on
     * the time interval that can elapse before the zone is no
     * longer authoritative.
     *
     * @var int|null
     */
    private $expire;

    /**
     * The unsigned 32 bit minimum TTL field that should be
     * exported with any RR from this zone.
     *
     * @var int|null
     */
    private $minimum;

    /**
     * @param int $expire
     */
    public function setExpire(int $expire): void
    {
        $this->expire = $expire;
    }

    /**
     * @return int
     */
    public function getExpire(): ?int
    {
        return $this->expire;
    }

    /**
     * @param int $minimum
     */
    public function setMinimum(int $minimum): void
    {
        $this->minimum = $minimum;
    }

    /**
     * @return int
     */
    public function getMinimum(): ?int
    {
        return $this->minimum;
    }

    /**
     * @param string $mname
     */
    public function setMname(string $mname): void
    {
        $this->mname = $mname;
    }

    /**
     * @return string
     */
    public function getMname(): ?string
    {
        return $this->mname;
    }

    /**
     * @param int $refresh
     */
    public function setRefresh(int $refresh): void
    {
        $this->refresh = $refresh;
    }

    /**
     * @return int
     */
    public function getRefresh(): ?int
    {
        return $this->refresh;
    }

    /**
     * @param int $retry
     */
    public function setRetry(int $retry): void
    {
        $this->retry = (int) $retry;
    }

    /**
     * @return int
     */
    public function getRetry(): ?int
    {
        return $this->retry;
    }

    /**
     * @param string $rname
     */
    public function setRname(string $rname): void
    {
        $this->rname = $rname;
    }

    /**
     * @return string
     */
    public function getRname(): ?string
    {
        return $this->rname;
    }

    /**
     * @param int $serial
     */
    public function setSerial(int $serial): void
    {
        $this->serial = $serial;
    }

    /**
     * @return int
     */
    public function getSerial(): ?int
    {
        return $this->serial;
    }

    /**
     * {@inheritdoc}
     */
    public function output(): string
    {
        return sprintf(
            '%s %s %s %s %s %s %s',
            $this->mname,
            $this->rname,
            $this->serial,
            $this->refresh,
            $this->retry,
            $this->expire,
            $this->minimum
        );
    }
}
