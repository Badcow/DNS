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

use PhpIP\IPBlock;

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
    public static function AAAA(string $address): AAAA
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
    public static function A(string $address): A
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
    public static function CNAME(string $cname): CNAME
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
    public static function HINFO(string $cpu, string $os): HINFO
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
    public static function MX(int $preference, string $exchange): MX
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
    public static function SOA(string $mname, string $rname, int $serial, int $refresh, int $retry, int $expire, int $minimum): SOA
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
    public static function NS(string $nsdname): NS
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
    public static function TXT(string $text): TXT
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
    public static function DNAME(string $target): DNAME
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
    public static function LOC(float $lat, float $lon, $alt = 0.0, $size = 1.0, $hp = 10000.0, $vp = 10.0): LOC
    {
        $loc = new LOC();
        $loc->setLatitude($lat);
        $loc->setLongitude($lon);
        $loc->setAltitude($alt);
        $loc->setSize($size);
        $loc->setHorizontalPrecision($hp);
        $loc->setVerticalPrecision($vp);

        return $loc;
    }

    /**
     * @param string $target
     *
     * @return PTR
     */
    public static function PTR(string $target): PTR
    {
        $ptr = new PTR();
        $ptr->setTarget($target);

        return $ptr;
    }

    /**
     * @param int    $flags
     * @param int    $algorithm
     * @param string $publicKey
     *
     * @return DNSKEY
     */
    public static function DNSKEY(int $flags, int $algorithm, string $publicKey): DNSKEY
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
    public static function DS(int $keyTag, int $algorithm, string $digest, int $digestType = DS::DIGEST_SHA1): DS
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
     * @param array  $types
     *
     * @return NSEC
     */
    public static function NSEC(string $nextDomainName, array $types): NSEC
    {
        $rdata = new NSEC();
        $rdata->setNextDomainName($nextDomainName);
        array_map([$rdata, 'addType'], $types);

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
    public static function RRSIG(string $typeCovered, int $algorithm, int $labels, int $originalTtl,
                                 int $signatureExpiration, int $signatureInception, int $keyTag,
                                 string $signersName, string $signature): RRSIG
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
    public static function SRV(int $priority, int $weight, int $port, string $target): SRV
    {
        $rdata = new SRV();
        $rdata->setPriority($priority);
        $rdata->setWeight($weight);
        $rdata->setPort($port);
        $rdata->setTarget($target);

        return $rdata;
    }

    /**
     * @param IPBlock[] $includedRanges
     * @param IPBlock[] $excludedRanges
     *
     * @return APL
     */
    public static function APL(array $includedRanges = [], array $excludedRanges = []): APL
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
    public static function CAA(int $flag, string $tag, string $value): CAA
    {
        $rdata = new CAA();
        $rdata->setFlag($flag);
        $rdata->setTag($tag);
        $rdata->setValue($value);

        return $rdata;
    }

    /**
     * @param int    $subType
     * @param string $hostname
     *
     * @return AFSDB
     */
    public static function AFSDB(int $subType, string $hostname): AFSDB
    {
        $afsdb = new AFSDB();
        $afsdb->setSubType($subType);
        $afsdb->setHostname($hostname);

        return $afsdb;
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

    public static function RP(string $mboxDomain, string $txtDomain): RP
    {
        $rp = new RP();
        $rp->setMailboxDomainName($mboxDomain);
        $rp->setTxtDomainName($txtDomain);

        return $rp;
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
