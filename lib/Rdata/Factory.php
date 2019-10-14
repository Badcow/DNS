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

        $className = __NAMESPACE__.'\\'.strtoupper($name);

        return new $className();
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public static function isTypeImplemented(string $name): bool
    {
        return class_exists(__NAMESPACE__.'\\'.strtoupper($name));
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
     * @param float $lat
     * @param float $lon
     * @param float $alt
     * @param float $size
     * @param float $hp
     * @param float $vp
     *
     * @return LOC
     */
    public static function Loc(float $lat, float $lon, $alt = 0.0, $size = 1.0, $hp = 10000.0, $vp = 10.0)
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
     * @param int    $digestType
     *
     * @return DS
     */
    public static function Ds(int $keyTag, int $algorithm, string $digest, int $digestType = DS::DIGEST_SHA1)
    {
        $rdata = new DS();
        $rdata->setKeyTag($keyTag);
        $rdata->setAlgorithm($algorithm);
        $rdata->setDigest($digest);
        $rdata->setDigestType($digestType);

        return $rdata;
    }

    /**
     * @param string $nextDomainName
     * @param array  $typeBitMaps
     *
     * @return NSEC
     */
    public static function Nsec(string $nextDomainName, array $typeBitMaps)
    {
        $rdata = new NSEC();
        $rdata->setNextDomainName($nextDomainName);
        array_map([$rdata, 'addTypeBitMap'], $typeBitMaps);

        return $rdata;
    }

    /**
     * @param string $typeCovered
     * @param int    $algorithm
     * @param int    $labels
     * @param int    $originalTtl
     * @param int    $signatureExpiration
     * @param int    $signatureInception
     * @param int    $keyTag
     * @param string $signersName
     * @param string $signature
     *
     * @return RRSIG
     */
    public static function Rrsig(string $typeCovered, int $algorithm, int $labels, int $originalTtl,
                            int $signatureExpiration, int $signatureInception, int $keyTag,
                            string $signersName, string $signature)
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
     * @param int    $priority
     * @param int    $weight
     * @param int    $port
     * @param string $target
     *
     * @return SRV
     */
    public static function Srv(int $priority, int $weight, int $port, string $target)
    {
        $rdata = new SRV();
        $rdata->setPriority($priority);
        $rdata->setWeight($weight);
        $rdata->setPort($port);
        $rdata->setTarget($target);

        return $rdata;
    }

    /**
     * @param \IPBlock[] $includedRanges
     * @param \IPBlock[] $excludedRanges
     *
     * @return APL
     */
    public static function Apl(array $includedRanges = [], array $excludedRanges = []): APL
    {
        $rdata = new APL();

        foreach ($includedRanges as $ipBlock) {
            $rdata->addAddressRange($ipBlock, true);
        }

        foreach ($excludedRanges as $ipBlock) {
            $rdata->addAddressRange($ipBlock, false);
        }

        return $rdata;
    }

    /**
     * @param int    $flag
     * @param string $tag
     * @param string $value
     *
     * @return CAA
     */
    public static function Caa(int $flag, string $tag, string $value): CAA
    {
        $rdata = new CAA();
        $rdata->setFlag($flag);
        $rdata->setTag($tag);
        $rdata->setValue($value);

        return $rdata;
    }

    public static function AFSDB(): AFSDB
    {
        // TODO: Implement AFSDB() method.
    }

    public static function CDNSKEY(): CDNSKEY
    {
        // TODO: Implement CDNSKEY() method.
    }

    public static function CDS(): CDS
    {
        // TODO: Implement CDS() method.
    }

    public static function CERT(): CERT
    {
        // TODO: Implement CERT() method.
    }

    public static function CSYNC(): CSYNC
    {
        // TODO: Implement CSYNC() method.
    }

    public static function DHCID(): DHCID
    {
        // TODO: Implement DHCID() method.
    }

    /**
     * @param int    $keyTag
     * @param int    $algorithm
     * @param string $digest
     * @param int    $digestType
     *
     * @return DLV
     */
    public static function DLV(int $keyTag, int $algorithm, string $digest, int $digestType = DS::DIGEST_SHA1): DLV
    {
        $rdata = new DLV();
        $rdata->setKeyTag($keyTag);
        $rdata->setAlgorithm($algorithm);
        $rdata->setDigest($digest);
        $rdata->setDigestType($digestType);

        return $rdata;
    }

    public static function HIP(): HIP
    {
        // TODO: Implement HIP() method.
    }

    public static function IPSECKEY(): IPSECKEY
    {
        // TODO: Implement IPSECKEY() method.
    }

    public static function KEY(): KEY
    {
        // TODO: Implement KEY() method.
    }

    public static function KX(): KX
    {
        // TODO: Implement KX() method.
    }

    public static function NAPTR(): NAPTR
    {
        // TODO: Implement NAPTR() method.
    }

    public static function NSEC3(): NSEC3
    {
        // TODO: Implement NSEC3() method.
    }

    public static function NSEC3PARAM(): NSEC3PARAM
    {
        // TODO: Implement NSEC3PARAM() method.
    }

    public static function OPENPGPKEY(): OPENPGPKEY
    {
        // TODO: Implement OPENPGPKEY() method.
    }

    public static function RP(): RP
    {
        // TODO: Implement RP() method.
    }

    public static function SIG(): SIG
    {
        // TODO: Implement SIG() method.
    }

    public static function SMIMEA(): SMIMEA
    {
        // TODO: Implement SMIMEA() method.
    }

    /**
     * @param int    $algorithm
     * @param int    $fpType
     * @param string $fingerprint
     *
     * @return SSHFP
     */
    public static function SSHFP(int $algorithm, int $fpType, string $fingerprint): SSHFP
    {
        $sshfp = new SSHFP();
        $sshfp->setAlgorithm($algorithm);
        $sshfp->setFingerprintType($fpType);
        $sshfp->setFingerprint($fingerprint);

        return $sshfp;
    }

    public static function TA(): TA
    {
        // TODO: Implement TA() method.
    }

    public static function TKEY(): TKEY
    {
        // TODO: Implement TKEY() method.
    }

    public static function TLSA(): TLSA
    {
        // TODO: Implement TLSA() method.
    }

    public static function TSIG(): TSIG
    {
        // TODO: Implement TSIG() method.
    }

    /**
     * @param int    $priority
     * @param int    $weight
     * @param string $target
     *
     * @return URI
     */
    public static function URI(int $priority, int $weight, string $target): URI
    {
        $uri = new URI();
        $uri->setPriority($priority);
        $uri->setWeight($weight);
        $uri->setTarget($target);

        return $uri;
    }
}
