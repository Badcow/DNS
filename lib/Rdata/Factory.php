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

use Badcow\DNS\Rdata\DNSSEC\DNSKEY;
use Badcow\DNS\Rdata\DNSSEC\DS;
use Badcow\DNS\Rdata\DNSSEC\NSEC;
use Badcow\DNS\Rdata\DNSSEC\RRSIG;

class Factory
{
    /**
     * Creates a new RData object from a name.
     *
     * @param string $name
     *
     * @throws UnsupportedTypeException
     *
     * @return RdataInterface
     */
    public static function newRdataFromName(string $name): RdataInterface
    {
        if (!self::isTypeImplemented($name)) {
            throw new UnsupportedTypeException($name);
        }

        $namespace = '\\Badcow\\DNS\\Rdata\\';
        $className = $namespace.strtoupper($name);

        if (!class_exists($className)) {
            $className = $namespace.'DNSSEC\\'.strtoupper($name);
        }

        return new $className();
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public static function isTypeImplemented(string $name): bool
    {
        $namespace = '\\Badcow\\DNS\\Rdata\\';
        $name = strtoupper($name);

        return class_exists($namespace.$name) || class_exists($namespace.'DNSSEC\\'.$name);
    }

    /**
     * Create a new AAAA R-Data object.
     *
     * @param string $address
     *
     * @return AAAA
     */
    public static function Aaaa($address)
    {
        $rdata = new AAAA();
        $rdata->setAddress($address);

        return $rdata;
    }

    /**
     * Create a new A R-Data object.
     *
     * @param string $address
     *
     * @return A
     */
    public static function A($address)
    {
        $rdata = new A();
        $rdata->setAddress($address);

        return $rdata;
    }

    /**
     * Create a new CNAME object.
     *
     * @param string $cname
     *
     * @return CNAME
     */
    public static function Cname($cname)
    {
        $rdata = new CNAME();
        $rdata->setTarget($cname);

        return $rdata;
    }

    /**
     * @param string $cpu
     * @param string $os
     *
     * @return HINFO
     */
    public static function Hinfo($cpu, $os)
    {
        $rdata = new HINFO();
        $rdata->setCpu($cpu);
        $rdata->setOs($os);

        return $rdata;
    }

    /**
     * @param int    $preference
     * @param string $exchange
     *
     * @return MX
     */
    public static function Mx($preference, $exchange)
    {
        $rdata = new MX();
        $rdata->setPreference($preference);
        $rdata->setExchange($exchange);

        return $rdata;
    }

    /**
     * @param string $mname
     * @param string $rname
     * @param int    $serial
     * @param int    $refresh
     * @param int    $retry
     * @param int    $expire
     * @param int    $minimum
     *
     * @return SOA
     */
    public static function Soa($mname, $rname, $serial, $refresh, $retry, $expire, $minimum)
    {
        $rdata = new SOA();
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
     *
     * @return NS
     */
    public static function Ns($nsdname)
    {
        $rdata = new NS();
        $rdata->setTarget($nsdname);

        return $rdata;
    }

    /**
     * @param string $text
     *
     * @return TXT
     */
    public static function txt($text)
    {
        $rdata = new TXT();
        $rdata->setText($text);

        return $rdata;
    }

    /**
     * @param string $target
     *
     * @return DNAME
     */
    public static function Dname($target)
    {
        $rdata = new DNAME();
        $rdata->setTarget($target);

        return $rdata;
    }

    /**
     * @param $lat
     * @param $lon
     * @param float $alt
     * @param float $size
     * @param float $hp
     * @param float $vp
     *
     * @return LOC
     */
    public static function Loc($lat, $lon, $alt = 0.0, $size = 1.0, $hp = 10000.0, $vp = 10.0)
    {
        $rdata = new LOC();
        $rdata->setLatitude($lat);
        $rdata->setLongitude($lon);
        $rdata->setAltitude($alt);
        $rdata->setSize($size);
        $rdata->setHorizontalPrecision($hp);
        $rdata->setVerticalPrecision($vp);

        return $rdata;
    }

    /**
     * @param string $target
     *
     * @return PTR
     */
    public static function Ptr($target)
    {
        $rdata = new PTR();
        $rdata->setTarget($target);

        return $rdata;
    }

    /**
     * @param int    $flags
     * @param int    $algorithm
     * @param string $publicKey
     *
     * @return DNSKEY
     */
    public static function Dnskey($flags, $algorithm, $publicKey)
    {
        $rdata = new DNSKEY();
        $rdata->setFlags($flags);
        $rdata->setAlgorithm($algorithm);
        $rdata->setPublicKey($publicKey);

        return $rdata;
    }

    /**
     * @param int    $keyTag
     * @param int    $algorithm
     * @param string $digest
     *
     * @return DS
     */
    public static function Ds($keyTag, $algorithm, $digest)
    {
        $rdata = new DS();
        $rdata->setKeyTag($keyTag);
        $rdata->setAlgorithm($algorithm);
        $rdata->setDigest($digest);

        return $rdata;
    }

    /**
     * @param $nextDomainName
     * @param array $typeBitMaps
     *
     * @return NSEC
     */
    public static function Nsec($nextDomainName, array $typeBitMaps)
    {
        $rdata = new NSEC();
        $rdata->setNextDomainName($nextDomainName);
        array_map([$rdata, 'addTypeBitMap'], $typeBitMaps);

        return $rdata;
    }

    /**
     * @param $typeCovered
     * @param $algorithm
     * @param $labels
     * @param $originalTtl
     * @param $signatureExpiration
     * @param $signatureInception
     * @param $keyTag
     * @param $signersName
     * @param $signature
     *
     * @return RRSIG
     */
    public static function Rrsig($typeCovered, $algorithm, $labels, $originalTtl,
                          $signatureExpiration, $signatureInception, $keyTag,
                          $signersName, $signature)
    {
        $rdata = new RRSIG();
        $rdata->setTypeCovered($typeCovered);
        $rdata->setAlgorithm($algorithm);
        $rdata->setLabels($labels);
        $rdata->setOriginalTtl($originalTtl);
        $rdata->setSignatureExpiration($signatureExpiration);
        $rdata->setSignatureInception($signatureInception);
        $rdata->setKeyTag($keyTag);
        $rdata->setSignersName($signersName);
        $rdata->setSignature($signature);

        return $rdata;
    }

    /**
     * @param $priority
     * @param $weight
     * @param $port
     * @param $target
     *
     * @return SRV
     */
    public static function Srv($priority, $weight, $port, $target)
    {
        $rdata = new SRV();
        $rdata->setPriority($priority);
        $rdata->setWeight($weight);
        $rdata->setPort($port);
        $rdata->setTarget($target);

        return $rdata;
    }
}
