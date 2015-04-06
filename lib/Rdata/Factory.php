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

class Factory
{
    /**
     * Create a new AAAA R-Data object
     *
     * @param string $address
     * @return AaaaRdata
     */
    public static function Aaaa($address)
    {
        $rdata = new AaaaRdata;
        $rdata->setAddress($address);

        return $rdata;
    }

    /**
     * Create a new A R-Data object
     *
     * @param string $address
     * @return ARdata
     */
    public static function A($address)
    {
        $rdata = new ARdata;
        $rdata->setAddress($address);

        return $rdata;
    }

    /**
     * Create a new CNAME object
     *
     * @param string $cname
     * @return CnameRdata
     */
    public static function Cname($cname)
    {
        $rdata = new CnameRdata();
        $rdata->setCname($cname);

        return $rdata;
    }

    /**
     * @param string $cpu
     * @param string $os
     * @return HinfoRdata
     */
    public static function Hinfo($cpu, $os)
    {
        $rdata = new HinfoRdata;
        $rdata->setCpu($cpu);
        $rdata->setOs($os);

        return $rdata;
    }

    /**
     * @param int $preference
     * @param string $exchange
     * @return MxRdata
     */
    public static function Mx($preference, $exchange)
    {
        $rdata = new MxRdata;
        $rdata->setPreference($preference);
        $rdata->setExchange($exchange);

        return $rdata;
    }

    /**
     * @param string $mname
     * @param string $rname
     * @param int $serial
     * @param int $refresh
     * @param int $retry
     * @param int $expire
     * @param int $minimum
     * @param bool $niceFormatting
     * @return NiceSoaRdata|SoaRdata
     */
    public static function Soa($mname, $rname, $serial, $refresh, $retry, $expire, $minimum, $niceFormatting = true)
    {
        $rdata = $niceFormatting ? new NiceSoaRdata : new SoaRdata;
        $rdata->setMname($mname);
        $rdata->setRname($rname);
        $rdata->setSerial($serial);
        $rdata->setRefresh($refresh);
        $rdata->setRetry($retry);
        $rdata->setExpire($expire);
        $rdata->setMinimum($minimum);

        return $rdata;
    }

    /**
     * @param string $nsdname
     * @return NsRdata
     */
    public static function Ns($nsdname)
    {
        $rdata = new NsRdata;
        $rdata->setNsdname($nsdname);

        return $rdata;
    }

    /**
     * @param string $text
     * @return TxtRdata
     */
    public static function txt($text)
    {
        $rdata = new TxtRdata;
        $rdata->setText($text);

        return $rdata;
    }
}
