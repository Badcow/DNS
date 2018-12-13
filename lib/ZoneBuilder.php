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

use Badcow\DNS\Rdata\{CNAME, MX, AAAA, SOA};
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
        $class = Classes::INTERNET;
        foreach ($zone as $rr) {
            if (null !== $class = $rr->getClass()) {
                break;
            }
        }

        foreach ($zone as &$rr) {
            $rr->setName(self::fullyQualify($rr->getName(), $zone->getName()));
            $rr->setTtl($rr->getTtl() ?? $zone->getDefaultTtl());

            if ($rr->getRdata() instanceof SOA) {
                $rr->getRdata()->setMname(self::fullyQualify($rr->getRdata()->getMname(), $zone->getName()));
                $rr->getRdata()->setRname(self::fullyQualify($rr->getRdata()->getRname(), $zone->getName()));
            }

            if ($rr->getRdata() instanceof CNAME) {
                $rr->getRdata()->setTarget(self::fullyQualify($rr->getRdata()->getTarget(), $zone->getName()));
            }

            if ($rr->getRdata() instanceof MX) {
                $rr->getRdata()->setExchange(self::fullyQualify($rr->getRdata()->getExchange(), $zone->getName()));
            }

            if ($rr->getRdata() instanceof AAAA) {
                $rr->getRdata()->setAddress(Toolbox::expandIpv6($rr->getRdata()->getAddress()));
            }

            $rr->setClass($class);
        }
    }

    /**
     * Add the parent domain to the sub-domain if the sub-domain if it is not fully qualified.
     *
     * @param string $subdomain
     * @param string $parent
     * @return string
     */
    private static function fullyQualify(string $subdomain, string $parent): string
    {
        if ('@' === $subdomain) {
            return $parent;
        }

        if ('.' !== substr($subdomain, -1, 1)) {
            return $subdomain.'.'.$parent;
        }

        return $subdomain;
    }
}
