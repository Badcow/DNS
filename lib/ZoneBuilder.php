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

            if ($rdata instanceof SOA) {
                $rdata->setMname(self::fullyQualify($rdata->getMname(), $zone->getName()));
                $rdata->setRname(self::fullyQualify($rdata->getRname(), $zone->getName()));
            }

            if ($rdata instanceof CNAME) {
                $rdata->setTarget(self::fullyQualify($rdata->getTarget(), $zone->getName()));
            }

            if ($rdata instanceof MX) {
                $rdata->setExchange(self::fullyQualify($rdata->getExchange(), $zone->getName()));
            }

            if ($rdata instanceof AAAA) {
                $rdata->setAddress(Toolbox::expandIpv6($rdata->getAddress()));
            }
        }
    }

    /**
     * Add the parent domain to the sub-domain if the sub-domain if it is not fully qualified.
     *
     * @param null|string $subDomain
     * @param string $parent
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
}
