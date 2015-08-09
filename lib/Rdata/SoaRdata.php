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

use Badcow\DNS\Validator;

/**
 * {@link http://www.ietf.org/rfc/rfc1035.text)
 */
class SoaRdata implements RdataInterface, FormattableInterface
{
    use RdataTrait, FormattableTrait;

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
     * @throws RdataException
     */
    public function setMname($mname)
    {
        if (!Validator::validateFqdn($mname)) {
            throw new RdataException(sprintf('MName "%s" is not a Fully Qualified Domain Name', $mname));
        }

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
     * @throws RdataException
     */
    public function setRname($rname)
    {
        if (!Validator::validateFqdn($rname)) {
            throw new RdataException(sprintf('RName "%s" is not a Fully Qualified Domain Name', $rname));
        }

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

    /**
     * {@inheritdoc}
     */
    public function outputFormatted()
    {
        $pad = $this->longestVarLength();
        $leftPadding = str_repeat(' ', $this->padding);

        return '(' . PHP_EOL .
            $leftPadding . str_pad($this->getMname(), $pad)   . ' ; MNAME' . PHP_EOL .
            $leftPadding . str_pad($this->getRname(), $pad)   . ' ; RNAME' . PHP_EOL .
            $leftPadding . str_pad($this->getSerial(), $pad)  . ' ; SERIAL' . PHP_EOL .
            $leftPadding . str_pad($this->getRefresh(), $pad) . ' ; REFRESH' . PHP_EOL .
            $leftPadding . str_pad($this->getRetry(), $pad)   . ' ; RETRY' . PHP_EOL .
            $leftPadding . str_pad($this->getExpire(), $pad)  . ' ; EXPIRE' . PHP_EOL .
            $leftPadding . str_pad($this->getMinimum(), $pad) . ' ; MINIMUM' . PHP_EOL .
            $leftPadding . ')';
    }

    /**
     * Determines the longest variable
     *
     * @return int
     */
    private function longestVarLength()
    {
        $l = 0;

        foreach (array(
                    $this->getMname(),
                    $this->getRname(),
                    $this->getSerial(),
                    $this->getRefresh(),
                    $this->getRetry(),
                    $this->getExpire(),
                    $this->getMinimum(),
                ) as $var) {
            $l = ($l < strlen($var)) ? strlen($var) : $l;
        }

        return $l;
    }
}
