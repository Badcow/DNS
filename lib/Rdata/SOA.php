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

use Badcow\DNS\Parser\TimeFormat;
use Badcow\DNS\Parser\Tokens;

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
    public function toText(): string
    {
        if (!isset($this->mname) ||
            !isset($this->rname) ||
            !isset($this->serial) ||
            !isset($this->refresh) ||
            !isset($this->retry) ||
            !isset($this->expire) ||
            !isset($this->minimum)) {
            throw new \InvalidArgumentException('All parameters of SOA must be set.');
        }

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
    public function toWire(): string
    {
        if (!isset($this->mname) ||
            !isset($this->rname) ||
            !isset($this->serial) ||
            !isset($this->refresh) ||
            !isset($this->retry) ||
            !isset($this->expire) ||
            !isset($this->minimum)) {
            throw new \InvalidArgumentException('All parameters of SOA must be set.');
        }

        return
            self::encodeName($this->mname).
            self::encodeName($this->rname).
            pack(
                'NNNNN',
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
    public static function fromText(string $text): RdataInterface
    {
        $rdata = explode(Tokens::SPACE, $text);
        $soa = new self();
        $soa->setMname($rdata[0]);
        $soa->setRname($rdata[1]);
        $soa->setSerial((int) $rdata[2]);
        $soa->setRefresh(TimeFormat::toSeconds($rdata[3]));
        $soa->setRetry(TimeFormat::toSeconds($rdata[4]));
        $soa->setExpire(TimeFormat::toSeconds($rdata[5]));
        $soa->setMinimum(TimeFormat::toSeconds($rdata[6]));

        return $soa;
    }

    /**
     * {@inheritdoc}
     */
    public static function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): RdataInterface
    {
        $soa = new self();
        $soa->setMname(self::decodeName($rdata, $offset));
        $soa->setRname(self::decodeName($rdata, $offset));
        $parameters = unpack('Nserial/Nrefresh/Nretry/Nexpire/Nminimum', $rdata, $offset);
        $soa->setSerial((int) $parameters['serial']);
        $soa->setRefresh(TimeFormat::toSeconds($parameters['refresh']));
        $soa->setRetry(TimeFormat::toSeconds($parameters['retry']));
        $soa->setExpire(TimeFormat::toSeconds($parameters['expire']));
        $soa->setMinimum(TimeFormat::toSeconds($parameters['minimum']));

        $offset += 20;

        return $soa;
    }
}
