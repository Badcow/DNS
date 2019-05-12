<?php

/*
 * This file is part of Badcow DNS Library.
 *
 * (c) Samuel Williams <sam@badcow.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Badcow\DNS;

use Badcow\DNS\Rdata\CNAME;
use Badcow\DNS\Rdata\MX;
use Badcow\DNS\Rdata\AAAA;
use Badcow\DNS\Rdata\RdataInterface;
use Badcow\DNS\Rdata\SOA;
use Badcow\DNS\Ip\Toolbox;

class ZoneBuilder
{
    /**
     * @param Zone $zone
     *
     * @return string
     */
    public static function build(Zone $zone): string
    {
        $master = '$ORIGIN '.$zone->getName().PHP_EOL;
        if (null !== $zone->getDefaultTtl()) {
            $master .= '$TTL '.$zone->getDefaultTtl().PHP_EOL;
        }

        foreach ($zone as $rr) {
            if (null !== $rr->getRdata()) {
                $master .= preg_replace('/\s+/', ' ', trim(sprintf('%s %s %s %s %s',
                    $rr->getName(),
                    $rr->getTtl(),
                    $rr->getClass(),
                    $rr->getType(),
                    $rr->getRdata()->output()
                )));
            }

            if (null !== $rr->getComment()) {
                $master .= '; '.$rr->getComment();
            }

            $master .= PHP_EOL;
        }

        return $master;
    }

    /**
     * Fills out all of the data of each resource record. The function will add the parent domain to all non-qualified
     * sub-domains, replace '@' with the zone name, ensure the class and TTL are on each record.
     *
     * @param Zone $zone
     */
    public static function fillOutZone(Zone $zone)
    {
        $class = $zone->getClass();

        foreach ($zone as &$rr) {
            $rr->setName(self::fullyQualify($rr->getName(), $zone->getName()));
            $rr->setTtl($rr->getTtl() ?? $zone->getDefaultTtl());
            $rr->setClass($class);
            $rdata = $rr->getRdata();
            static::fillOutRdata($rdata, $zone);
        }
    }

    /**
     * Add the parent domain to the sub-domain if the sub-domain if it is not fully qualified.
     *
     * @param string|null $subDomain
     * @param string      $parent
     *
     * @return string
     */
    private static function fullyQualify(?string $subDomain, string $parent): string
    {
        if ('@' === $subDomain || null === $subDomain) {
            return $parent;
        }

        if ('.' !== substr($subDomain, -1, 1)) {
            return $subDomain.'.'.$parent;
        }

        return $subDomain;
    }

    /**
     * @param RdataInterface $rdata
     * @param Zone           $zone
     */
    private static function fillOutRdata(RdataInterface $rdata, Zone $zone): void
    {
        $mappings = [
            SOA::class => 'static::fillOutSoa',
            Cname::class => 'static::fillOutCname',
            MX::class => 'static::fillOutMx',
            AAAA::class => 'static::fillOutAaaa',
        ];

        foreach ($mappings as $class => $callable) {
            if (!is_callable($callable)) {
                throw new \InvalidArgumentException(sprintf('The argument "%s" is not callable.', $callable));
            }

            if ($rdata instanceof $class) {
                call_user_func($callable, $rdata, $zone);
            }
        }
    }

    /**
     * @param SOA  $rdata
     * @param Zone $zone
     */
    private static function fillOutSoa(SOA $rdata, Zone $zone): void
    {
        $rdata->setMname(self::fullyQualify($rdata->getMname(), $zone->getName()));
        $rdata->setRname(self::fullyQualify($rdata->getRname(), $zone->getName()));
    }

    /**
     * @param CNAME $rdata
     * @param Zone  $zone
     */
    private static function fillOutCname(Cname $rdata, Zone $zone): void
    {
        $rdata->setTarget(self::fullyQualify($rdata->getTarget(), $zone->getName()));
    }

    /**
     * @param MX   $rdata
     * @param Zone $zone
     */
    private static function fillOutMx(MX $rdata, Zone $zone): void
    {
        $rdata->setExchange(self::fullyQualify($rdata->getExchange(), $zone->getName()));
    }

    /**
     * @param AAAA $rdata
     * @param Zone $zone
     */
    private static function fillOutAaaa(AAAA $rdata, Zone $zone): void
    {
        $rdata->setAddress(Toolbox::expandIpv6($rdata->getAddress()));
    }
}
