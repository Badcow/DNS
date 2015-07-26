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

use Badcow\DNS\Rdata\AaaaRdata;
use Badcow\DNS\Rdata\ARdata;
use Badcow\DNS\Rdata\CnameRdata;
use Badcow\DNS\Rdata\DnameRdata;
use Badcow\DNS\Rdata\HinfoRdata;
use Badcow\DNS\Rdata\LocRdata;
use Badcow\DNS\Rdata\MxRdata;
use Badcow\DNS\Rdata\NiceSoaRdata;
use Badcow\DNS\Rdata\NsRdata;
use Badcow\DNS\Rdata\SoaRdata;
use Badcow\DNS\Rdata\TxtRdata;

class AlignedBuilder implements ZoneBuilderInterface
{
    private static $order = array(
        SoaRdata::TYPE,
        NsRdata::TYPE,
        ARdata::TYPE,
        AaaaRdata::TYPE,
        CnameRdata::TYPE,
        DnameRdata::TYPE,
        MxRdata::TYPE,
        LocRdata::TYPE,
        HinfoRdata::TYPE,
        TxtRdata::TYPE,
    );

    /**
     * {@inheritdoc}
     */
    public function build(ZoneInterface $zone)
    {
        $master = sprintf(
            "\$ORIGIN %s\n\$TTL %s\n",
            $zone->getName(),
            $zone->getDefaultTtl()
        );

        $rrs = $zone->getResourceRecords();
        usort($rrs, 'self::compareResourceRecords');
        $current = SoaRdata::TYPE;

        $namePadding = $ttlPadding = $typePadding = 0;

        foreach ($rrs as $rr) {
            /* @var $rr ResourceRecord */
            $namePadding = (strlen($rr->getName()) > $namePadding) ? strlen($rr->getName()) : $namePadding;
            $ttlPadding = (strlen($rr->getTtl()) > $ttlPadding) ? strlen($rr->getTtl()) : $ttlPadding;
            $typePadding = (strlen($rr->getType()) > $typePadding) ? strlen($rr->getType()) : $typePadding;
        }

        foreach ($rrs as $rr) {
            /* @var $rr ResourceRecord */
            if ($rr->getType() !== $current) {
                $master .= "\n; " . $rr->getType() . " RECORDS\n";
                $current = $rr->getType();
            }

            if ($rr->getRdata() instanceof SoaRdata) {
                $rr->setRdata($this->niceSoa($rr->getRdata()));
                $rr->getRdata()->setPadding($namePadding + $ttlPadding + $typePadding + 6);
            }

            $master .= sprintf("%s %s %s %s %s",
                str_pad($rr->getName(), $namePadding, ' ', STR_PAD_RIGHT),
                str_pad($rr->getTtl(), $ttlPadding, ' ', STR_PAD_RIGHT),
                $rr->getClass(),
                str_pad($rr->getType(), $typePadding, ' ', STR_PAD_RIGHT),
                $rr->getRdata()->output()
            );

            $master .= (null == $rr->getComment()) ? "\n" : sprintf("; %s\n", $rr->getComment());
        }

        return $master;
    }

    /**
     * @param ResourceRecord $a
     * @param ResourceRecord $b
     * @return int
     */
    public static function compareResourceRecords(ResourceRecord $a, ResourceRecord $b)
    {
        if ($a->getType() === $b->getType()) {
            return strcmp($a->getName() . $a->getRdata()->output(), $b->getName() . $b->getRdata()->output());
        }

        $_a = array_search($a->getType(), self::$order);
        $_b = array_search($b->getType(), self::$order);

        if ($_a !== false && $_b !== false) {
            return $_a - $_b;
        }

        if ($_a === false) {
            return 1;
        }

        return -1;
    }

    /**
     * Converts an SoaRdata to human friendly NiceSoaRdata
     *
     * @param SoaRdata $soa
     * @return NiceSoaRdata
     */
    private function niceSoa(SoaRdata $soa)
    {
        $niceSoa = new NiceSoaRdata();
        $niceSoa->setMname($soa->getMname());
        $niceSoa->setRname($soa->getRname());
        $niceSoa->setSerial($soa->getSerial());
        $niceSoa->setRefresh($soa->getRefresh());
        $niceSoa->setRetry($soa->getRetry());
        $niceSoa->setExpire($soa->getExpire());
        $niceSoa->setMinimum($soa->getMinimum());

        return $niceSoa;
    }
}
