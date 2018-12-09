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

use Badcow\DNS\ResourceRecord;

/**
 * @see http://www.ietf.org/rfc/rfc1035.text
 */
class SOA implements RdataInterface
{
    use RdataTrait;

    const TYPE = 'SOA';

    /**
     * The <domain-name> of the name server that was the
     * original or primary source of data for this zone.
     *
     * @var string
     */
    private $mname;

    /**
     * A <domain-name> which specifies the mailbox of the
     * person responsible for this zone.
     *
     * @var string
     */
    private $rname;

    /**
     * The unsigned 32 bit version number of the original copy
     * of the zone.
     *
     * @var int
     */
    private $serial;

    /**
     * A 32 bit time interval before the zone should be
     * refreshed.
     *
     * @var int
     */
    private $refresh;

    /**
     * A 32 bit time interval that should elapse before a
     * failed refresh should be retried.
     *
     * @var int
     */
    private $retry;

    /**
     * A 32 bit time value that specifies the upper limit on
     * the time interval that can elapse before the zone is no
     * longer authoritative.
     *
     * @var int
     */
    private $expire;

    /**
     * The unsigned 32 bit minimum TTL field that should be
     * exported with any RR from this zone.
     *
     * @var int
     */
    private $minimum;

    /**
     * @param $expire
     */
    public function setExpire($expire)
    {
        $this->expire = (int) $expire;
    }

    /**
     * @return int
     */
    public function getExpire()
    {
        return $this->expire;
    }

    /**
     * @param $minimum
     */
    public function setMinimum($minimum)
    {
        $this->minimum = (int) $minimum;
    }

    /**
     * @return int
     */
    public function getMinimum()
    {
        return $this->minimum;
    }

    /**
     * @param $mname
     */
    public function setMname($mname)
    {
        $this->mname = $mname;
    }

    /**
     * @return string
     */
    public function getMname()
    {
        return $this->mname;
    }

    /**
     * @param $refresh
     */
    public function setRefresh($refresh)
    {
        $this->refresh = (int) $refresh;
    }

    /**
     * @return int
     */
    public function getRefresh()
    {
        return $this->refresh;
    }

    /**
     * @param $retry
     */
    public function setRetry($retry)
    {
        $this->retry = (int) $retry;
    }

    /**
     * @return int
     */
    public function getRetry()
    {
        return $this->retry;
    }

    /**
     * @param $rname
     */
    public function setRname($rname)
    {
        $this->rname = $rname;
    }

    /**
     * @return string
     */
    public function getRname()
    {
        return $this->rname;
    }

    /**
     * @param int $serial
     */
    public function setSerial($serial)
    {
        $this->serial = (int) $serial;
    }

    /**
     * @return int
     */
    public function getSerial()
    {
        return $this->serial;
    }

    /**
     * {@inheritdoc}
     */
    public function output()
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
