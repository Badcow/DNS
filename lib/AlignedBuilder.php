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

use Badcow\DNS\Rdata\AAAA;
use Badcow\DNS\Rdata\A;
use Badcow\DNS\Rdata\CNAME;
use Badcow\DNS\Rdata\DNAME;
use Badcow\DNS\Rdata\FormattableInterface;
use Badcow\DNS\Rdata\HINFO;
use Badcow\DNS\Rdata\LOC;
use Badcow\DNS\Rdata\MX;
use Badcow\DNS\Rdata\NS;
use Badcow\DNS\Rdata\PTR;
use Badcow\DNS\Rdata\SOA;
use Badcow\DNS\Rdata\SRV;
use Badcow\DNS\Rdata\TXT;

class AlignedBuilder implements ZoneBuilderInterface
{
    /**
     * The order in which Resource Records should appear in a zone.
     *
     * @var array
     */
    private static $order = [
        SOA::TYPE,
        NS::TYPE,
        A::TYPE,
        AAAA::TYPE,
        CNAME::TYPE,
        DNAME::TYPE,
        MX::TYPE,
        LOC::TYPE,
        HINFO::TYPE,
        TXT::TYPE,
        PTR::TYPE,
        SRV::TYPE,
    ];

    /**
     * {@inheritdoc}
     */
    public function build(ZoneInterface $zone)
    {
        $master = '$ORIGIN '.$zone->getName().PHP_EOL.
                    '$TTL '.$zone->getDefaultTtl().PHP_EOL;

        $rrs = $zone->getResourceRecords();
        $current = SOA::TYPE;
        $namePadding = $ttlPadding = $typePadding = 0;
        usort($rrs, 'self::compareResourceRecords');

        foreach ($rrs as $rr) {
            /* @var $rr ResourceRecord */
            $namePadding = (strlen($rr->getName()) > $namePadding) ? strlen($rr->getName()) : $namePadding;
            $ttlPadding = (strlen($rr->getTtl()) > $ttlPadding) ? strlen($rr->getTtl()) : $ttlPadding;
            $typePadding = (strlen($rr->getType()) > $typePadding) ? strlen($rr->getType()) : $typePadding;
        }

        foreach ($rrs as $rr) {
            /* @var $rr ResourceRecord */
            if (null == $rr->getRdata()) {
                continue;
            }

            if ($rr->getType() !== $current) {
                $master .= PHP_EOL.ResourceRecord::COMMENT_DELIMINATOR.$rr->getType().' RECORDS'.PHP_EOL;
                $current = $rr->getType();
            }

            $rdata = $rr->getRdata();

            if ($rdata instanceof FormattableInterface) {
                $rdata->setPadding($namePadding + $ttlPadding + $typePadding + 6);
            }

            $master .= sprintf('%s %s %s %s %s',
                str_pad($rr->getName(), $namePadding, ' ', STR_PAD_RIGHT),
                str_pad($rr->getTtl(), $ttlPadding, ' ', STR_PAD_RIGHT),
                str_pad($rr->getClass(), 2, ' ', STR_PAD_RIGHT),
                str_pad($rr->getType(), $typePadding, ' ', STR_PAD_RIGHT),
                ($rdata instanceof FormattableInterface) ? $rdata->outputFormatted() : $rdata->output()
            );

            if (null != $rr->getComment()) {
                $master .= ResourceRecord::COMMENT_DELIMINATOR.$rr->getComment();
            }

            $master .= PHP_EOL;
        }

        return $master;
    }

    /**
     * Compares two ResourceRecords to determine which is the higher order. Used with the usort() function.
     *
     * @param ResourceRecord $a
     * @param ResourceRecord $b
     *
     * @return int
     */
    public static function compareResourceRecords(ResourceRecord $a, ResourceRecord $b)
    {
        if ($a->getType() === $b->getType()) {
            return strcmp($a->getName().$a->getRdata()->output(), $b->getName().$b->getRdata()->output());
        }

        $_a = array_search($a->getType(), self::$order);
        $_b = array_search($b->getType(), self::$order);

        if (false !== $_a && false !== $_b) {
            return $_a - $_b;
        }

        if (false === $_a) {
            return 1;
        }

        return -1;
    }
}
