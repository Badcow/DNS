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

namespace Badcow\DNS;

use Badcow\DNS\Parser\Tokens;
use Badcow\DNS\Rdata\AAAA;
use Badcow\DNS\Rdata\CNAME;
use Badcow\DNS\Rdata\DNAME;
use Badcow\DNS\Rdata\MX;
use Badcow\DNS\Rdata\NS;
use Badcow\DNS\Rdata\PTR;
use Badcow\DNS\Rdata\RdataInterface;
use Badcow\DNS\Rdata\SOA;
use Badcow\DNS\Rdata\SRV;

class ZoneBuilder
{
    /**
     * Mapping of Rdata types to functions that fill them out.
     *
     * @var callable[]
     */
    private static $rdataFillers = [
        SOA::TYPE => __CLASS__.'::fillOutSoa',
        CNAME::TYPE => __CLASS__.'::fillOutCname',
        DNAME::TYPE => __CLASS__.'::fillOutCname',
        SRV::TYPE => __CLASS__.'::fillOutSrv',
        NS::TYPE => __CLASS__.'::fillOutCname',
        PTR::TYPE => __CLASS__.'::fillOutCname',
        MX::TYPE => __CLASS__.'::fillOutMx',
        AAAA::TYPE => __CLASS__.'::fillOutAaaa',
    ];

    public static function build(Zone $zone): string
    {
        $master = '$ORIGIN '.$zone->getName().Tokens::LINE_FEED;
        if (null !== $zone->getDefaultTtl()) {
            $master .= '$TTL '.$zone->getDefaultTtl().Tokens::LINE_FEED;
        }

        foreach ($zone as $rr) {
            if (null !== $rr->getRdata()) {
                $master .= preg_replace('/\s+/', ' ', trim(sprintf(
                    '%s %s %s %s %s',
                    $rr->getName(),
                    $rr->getTtl(),
                    $rr->getClass(),
                    $rr->getType(),
                    $rr->getRdata()->toText()
                )));
            }

            if (null !== $rr->getComment()) {
                $master .= Tokens::SEMICOLON.Tokens::SPACE.$rr->getComment();
            }

            $master .= Tokens::LINE_FEED;
        }

        return $master;
    }

    /**
     * Fills out all of the data of each resource record. The function will add the parent domain to all non-qualified
     * sub-domains, replace '@' with the zone name, ensure the class and TTL are on each record.
     */
    public static function fillOutZone(Zone $zone): void
    {
        $class = $zone->getClass();

        foreach ($zone as &$rr) {
            $rr->setName(self::fullyQualify($rr->getName(), $zone->getName()));
            $rr->setTtl($rr->getTtl() ?? $zone->getDefaultTtl());
            $rr->setClass($class);
            static::fillOutRdata($rr->getRdata(), $zone);
        }
    }

    /**
     * Add the parent domain to the sub-domain if the sub-domain if it is not fully qualified.
     */
    protected static function fullyQualify(?string $subDomain, string $parent): string
    {
        if ('@' === $subDomain || null === $subDomain) {
            return $parent;
        }

        if ('.' !== substr($subDomain, -1, 1)) {
            return $subDomain.'.'.$parent;
        }

        return $subDomain;
    }

    protected static function fillOutRdata(RdataInterface $rdata, Zone $zone): void
    {
        if (array_key_exists($rdata->getType(), self::$rdataFillers)) {
            call_user_func(self::$rdataFillers[$rdata->getType()], $rdata, $zone);
        }
    }

    protected static function fillOutSoa(SOA $rdata, Zone $zone): void
    {
        $rdata->setMname(self::fullyQualify($rdata->getMname(), $zone->getName()));
        $rdata->setRname(self::fullyQualify($rdata->getRname(), $zone->getName()));
    }

    protected static function fillOutCname(Cname $rdata, Zone $zone): void
    {
        $rdata->setTarget(self::fullyQualify($rdata->getTarget(), $zone->getName()));
    }

    protected static function fillOutSrv(SRV $rdata, Zone $zone): void
    {
        $rdata->setTarget(self::fullyQualify($rdata->getTarget(), $zone->getName()));
    }

    protected static function fillOutMx(MX $rdata, Zone $zone): void
    {
        $rdata->setExchange(self::fullyQualify($rdata->getExchange(), $zone->getName()));
    }

    protected static function fillOutAaaa(AAAA $rdata): void
    {
        $rdata->setAddress(PTR::expandIpv6($rdata->getAddress() ?? ''));
    }
}
