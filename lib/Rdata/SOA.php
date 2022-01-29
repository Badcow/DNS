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
use Badcow\DNS\Parser\TimeFormat;
use Badcow\DNS\Parser\Tokens;
use InvalidArgumentException;

/**
 * @see https://tools.ietf.org/html/rfc1035#section-3.3.13
 */
class SOA implements RdataInterface
{
    use RdataTrait;

    public const TYPE = 'SOA';
    public const TYPE_CODE = 6;

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

    public function toText(): string
    {
        if (!isset($this->mname) ||
            !isset($this->rname) ||
            !isset($this->serial) ||
            !isset($this->refresh) ||
            !isset($this->retry) ||
            !isset($this->expire) ||
            !isset($this->minimum)) {
            throw new InvalidArgumentException('All parameters of SOA must be set.');
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

    public function toWire(): string
    {
        if (!isset($this->mname) ||
            !isset($this->rname) ||
            !isset($this->serial) ||
            !isset($this->refresh) ||
            !isset($this->retry) ||
            !isset($this->expire) ||
            !isset($this->minimum)) {
            throw new InvalidArgumentException('All parameters of SOA must be set.');
        }

        return
            Message::encodeName($this->mname).
            Message::encodeName($this->rname).
            pack(
                'NNNNN',
                $this->serial,
                $this->refresh,
                $this->retry,
                $this->expire,
                $this->minimum
            );
    }

    public function fromText(string $text): void
    {
        $rdata = explode(Tokens::SPACE, $text);

        $this->setMname($rdata[0]);
        $this->setRname($rdata[1]);
        $this->setSerial((int) $rdata[2]);
        $this->setRefresh(TimeFormat::toSeconds($rdata[3]));
        $this->setRetry(TimeFormat::toSeconds($rdata[4]));
        $this->setExpire(TimeFormat::toSeconds($rdata[5]));
        $this->setMinimum(TimeFormat::toSeconds($rdata[6]));
    }

    public function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): void
    {
        $this->setMname(Message::decodeName($rdata, $offset));
        $this->setRname(Message::decodeName($rdata, $offset));
        if (false === $parameters = unpack('Nserial/Nrefresh/Nretry/Nexpire/Nminimum', $rdata, $offset)) {
            throw new DecodeException(static::TYPE, $rdata);
        }

        $this->setSerial((int) $parameters['serial']);
        $this->setRefresh(TimeFormat::toSeconds($parameters['refresh']));
        $this->setRetry(TimeFormat::toSeconds($parameters['retry']));
        $this->setExpire(TimeFormat::toSeconds($parameters['expire']));
        $this->setMinimum(TimeFormat::toSeconds($parameters['minimum']));

        $offset += 20;
    }
}
