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

use Badcow\DNS\Parser\ParseException;
use Badcow\DNS\Parser\Tokens;
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

        $className = self::getRdataClassName($name);

        return new $className();
    }

    /**
     * @param int $id
     *
     * @return RdataInterface
     *
     * @throws UnsupportedTypeException
     */
    public static function newRdataFromId(int $id): RdataInterface
    {
        return self::newRdataFromName(Types::getName($id));
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public static function isTypeImplemented(string $name): bool
    {
        return class_exists(self::getRdataClassName($name));
    }

    /**
     * @param int $typeCode
     *
     * @return bool
     */
    public static function isTypeCodeImplemented(int $typeCode): bool
    {
        try {
            return self::isTypeImplemented(Types::getName($typeCode));
        } catch (UnsupportedTypeException $e) {
            return false;
        }
    }

    /**
     * @param string $type
     * @param string $text
     *
     * @return RdataInterface
     *
     * @throws ParseException
     * @throws UnsupportedTypeException
     */
    public static function textToRdataType(string $type, string $text): RdataInterface
    {
        if (1 === preg_match('/^TYPE(\d+)$/', $type, $matches)) {
            $typeCode = (int) $matches[1];
            if (self::isTypeCodeImplemented($typeCode)) {
                $type = Types::getName($typeCode);
            } else {
                $rdata = UnknownType::fromText($text);
                $rdata->setTypeCode($typeCode);

                return $rdata;
            }
        }

        if (!self::isTypeImplemented($type)) {
            return new PolymorphicRdata($type, $text);
        }

        if (1 === preg_match('/^\\\#\s+(\d+)(\s[a-f0-9\s]+)?$/i', $text, $matches)) {
            if ('0' === $matches[1]) {
                $className = self::getRdataClassName($type);

                return new $className();
            }
            $wireFormat = hex2bin(str_replace(Tokens::SPACE, '', $matches[2]));

            /** @var callable $callable */
            $callable = self::getRdataClassName($type).'::fromWire';

            return call_user_func($callable, $wireFormat);
        }

        /** @var callable $callable */
        $callable = self::getRdataClassName($type).'::fromText';

        return call_user_func($callable, $text);
    }

    /**
     * Get the fully qualified class name of the RData class for $type.
     *
     * @param string $type
     *
     * @return string
     */
    public static function getRdataClassName(string $type): string
    {
        return __NAMESPACE__.'\\'.strtoupper($type);
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
        $txt = new TXT();
        $txt->setText($text);

        return $txt;
    }

    /**
     * @param string $text
     *
     * @return SPF
     */
    public static function SPF(string $text): SPF
    {
        $spf = new SPF();
        $spf->setText($text);

        return $spf;
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
     * @param string    $typeCovered
     * @param int       $algorithm
     * @param int       $labels
     * @param int       $originalTtl
     * @param \DateTime $signatureExpiration
     * @param \DateTime $signatureInception
     * @param int       $keyTag
     * @param string    $signersName
     * @param string    $signature
     *
     * @return RRSIG
     */
    public static function RRSIG(string $typeCovered, int $algorithm, int $labels, int $originalTtl,
                                    \DateTime $signatureExpiration, \DateTime $signatureInception, int $keyTag,
                                    string $signersName, string $signature): RRSIG
    {
        $rrsig = new RRSIG();
        $rrsig->setTypeCovered($typeCovered);
        $rrsig->setAlgorithm($algorithm);
        $rrsig->setLabels($labels);
        $rrsig->setOriginalTtl($originalTtl);
        $rrsig->setSignatureExpiration($signatureExpiration);
        $rrsig->setSignatureInception($signatureInception);
        $rrsig->setKeyTag($keyTag);
        $rrsig->setSignersName($signersName);
        $rrsig->setSignature($signature);

        return $rrsig;
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

    /**
     * @param int    $flags
     * @param int    $algorithm
     * @param string $publicKey
     *
     * @return CDNSKEY
     */
    public static function CDNSKEY(int $flags, int $algorithm, string $publicKey): CDNSKEY
    {
        $cdnskey = new CDNSKEY();
        $cdnskey->setFlags($flags);
        $cdnskey->setAlgorithm($algorithm);
        $cdnskey->setPublicKey($publicKey);

        return $cdnskey;
    }

    /**
     * @param int    $keyTag
     * @param int    $algorithm
     * @param string $digest
     * @param int    $digestType
     *
     * @return CDS
     */
    public static function CDS(int $keyTag, int $algorithm, string $digest, int $digestType = DS::DIGEST_SHA1): CDS
    {
        $cds = new CDS();
        $cds->setKeyTag($keyTag);
        $cds->setAlgorithm($algorithm);
        $cds->setDigest($digest);
        $cds->setDigestType($digestType);

        return $cds;
    }

    /**
     * @param int|string $certificateType
     * @param int        $keyTag
     * @param int        $algorithm
     * @param string     $certificate
     *
     * @return CERT
     */
    public static function CERT($certificateType, int $keyTag, int $algorithm, string $certificate): CERT
    {
        $cert = new CERT();
        $cert->setCertificateType($certificateType);
        $cert->setKeyTag($keyTag);
        $cert->setAlgorithm($algorithm);
        $cert->setCertificate($certificate);

        return $cert;
    }

    public static function CSYNC(int $soaSerial, int $flags, array $types): CSYNC
    {
        $csync = new CSYNC();
        $csync->setSoaSerial($soaSerial);
        $csync->setFlags($flags);
        array_map([$csync, 'addType'], $types);

        return $csync;
    }

    /**
     * @param string|null $digest         Set to &null if the $identifier and $fqdn are known
     * @param int         $identifierType 16-bit integer
     * @param string|null $identifier     This is ignored if $digest is not &null
     * @param string|null $fqdn           This is ignored if $digest is not &null
     *
     * @return DHCID
     */
    public static function DHCID(?string $digest, int $identifierType, ?string $identifier = null, ?string $fqdn = null): DHCID
    {
        $dhcid = new DHCID();
        if (null !== $digest) {
            $dhcid->setIdentifierType($identifierType);
            $dhcid->setDigest($digest);

            return $dhcid;
        }

        if (null === $identifier || null === $fqdn) {
            throw new \InvalidArgumentException('Identifier and FQDN cannot be null if digest is null.');
        }

        $dhcid->setIdentifier($identifierType, $identifier);
        $dhcid->setFqdn($fqdn);

        return $dhcid;
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

    /**
     * @param int      $publicKeyAlgorithm
     * @param string   $hostIdentityTag
     * @param string   $publicKey
     * @param string[] $rendezvousServers
     *
     * @return HIP
     */
    public static function HIP(int $publicKeyAlgorithm, string $hostIdentityTag, string $publicKey, array $rendezvousServers): HIP
    {
        $hip = new HIP();
        $hip->setPublicKeyAlgorithm($publicKeyAlgorithm);
        $hip->setHostIdentityTag($hostIdentityTag);
        $hip->setPublicKey($publicKey);
        array_map([$hip, 'addRendezvousServer'], $rendezvousServers);

        return $hip;
    }

    /**
     * @param int         $precedence an 8-bit unsigned integer
     * @param string|null $gateway    either &null for no gateway, a fully qualified domain name, or an IPv4 or IPv6 address
     * @param int         $algorithm  either IPSECKEY::ALGORITHM_NONE, IPSECKEY::ALGORITHM_DSA, IPSECKEY::ALGORITHM_RSA, or IPSECKEY::ALGORITHM_ECDSA
     * @param string|null $publicKey  base64 encoded public key
     *
     * @return IPSECKEY
     */
    public static function IPSECKEY(int $precedence, ?string $gateway, int $algorithm = 0, ?string $publicKey = null): IPSECKEY
    {
        $ipseckey = new IPSECKEY();
        $ipseckey->setPrecedence($precedence);
        $ipseckey->setGateway($gateway);
        $ipseckey->setPublicKey($algorithm, $publicKey);

        return $ipseckey;
    }

    /**
     * @param int    $flags
     * @param int    $protocol
     * @param int    $algorithm
     * @param string $publicKey
     *
     * @return KEY
     */
    public static function KEY(int $flags, int $protocol, int $algorithm, string $publicKey): KEY
    {
        $key = new KEY();
        $key->setFlags($flags);
        $key->setProtocol($protocol);
        $key->setAlgorithm($algorithm);
        $key->setPublicKey($publicKey);

        return $key;
    }

    /**
     * @param int    $preference
     * @param string $exchanger
     *
     * @return KX
     */
    public static function KX(int $preference, string $exchanger): KX
    {
        $kx = new KX();
        $kx->setPreference($preference);
        $kx->setExchanger($exchanger);

        return $kx;
    }

    /**
     * @param int    $order
     * @param int    $preference
     * @param string $flags
     * @param string $services
     * @param string $regexp
     * @param string $replacement
     *
     * @return NAPTR
     */
    public static function NAPTR(int $order, int $preference, string $flags, string $services, string $regexp, string $replacement): NAPTR
    {
        $naptr = new NAPTR();
        $naptr->setOrder($order);
        $naptr->setPreference($preference);
        $naptr->setFlags($flags);
        $naptr->setServices($services);
        $naptr->setRegexp($regexp);
        $naptr->setReplacement($replacement);

        return $naptr;
    }

    /**
     * @param int    $hashAlgorithm
     * @param bool   $unsignedDelegationsCovered
     * @param int    $iterations
     * @param string $salt
     * @param string $nextHashedOwnerName
     * @param array  $types
     *
     * @return NSEC3
     */
    public static function NSEC3(int $hashAlgorithm, bool $unsignedDelegationsCovered, int $iterations, string $salt, string $nextHashedOwnerName, array $types): NSEC3
    {
        $nsec3 = new NSEC3();
        $nsec3->setHashAlgorithm($hashAlgorithm);
        $nsec3->setUnsignedDelegationsCovered($unsignedDelegationsCovered);
        $nsec3->setIterations($iterations);
        $nsec3->setSalt($salt);
        $nsec3->setNextHashedOwnerName($nextHashedOwnerName);
        array_map([$nsec3, 'addType'], $types);

        return $nsec3;
    }

    /**
     * @param int    $hashAlgorithm
     * @param int    $flags
     * @param int    $iterations
     * @param string $salt
     *
     * @return NSEC3PARAM
     */
    public static function NSEC3PARAM(int $hashAlgorithm, int $flags, int $iterations, string $salt): NSEC3PARAM
    {
        $nsec3param = new NSEC3PARAM();
        $nsec3param->setHashAlgorithm($hashAlgorithm);
        $nsec3param->setFlags($flags);
        $nsec3param->setIterations($iterations);
        $nsec3param->setSalt($salt);

        return $nsec3param;
    }

    public static function RP(string $mboxDomain, string $txtDomain): RP
    {
        $rp = new RP();
        $rp->setMailboxDomainName($mboxDomain);
        $rp->setTxtDomainName($txtDomain);

        return $rp;
    }

    /**
     * @param string    $typeCovered
     * @param int       $algorithm
     * @param int       $labels
     * @param int       $originalTtl
     * @param \DateTime $signatureExpiration
     * @param \DateTime $signatureInception
     * @param int       $keyTag
     * @param string    $signersName
     * @param string    $signature
     *
     * @return SIG
     */
    public static function SIG(string $typeCovered, int $algorithm, int $labels, int $originalTtl,
                                 \DateTime $signatureExpiration, \DateTime $signatureInception, int $keyTag,
                                 string $signersName, string $signature): SIG
    {
        $sig = new SIG();
        $sig->setTypeCovered($typeCovered);
        $sig->setAlgorithm($algorithm);
        $sig->setLabels($labels);
        $sig->setOriginalTtl($originalTtl);
        $sig->setSignatureExpiration($signatureExpiration);
        $sig->setSignatureInception($signatureInception);
        $sig->setKeyTag($keyTag);
        $sig->setSignersName($signersName);
        $sig->setSignature($signature);

        return $sig;
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

    /**
     * @param int    $keyTag
     * @param int    $algorithm
     * @param string $digest
     * @param int    $digestType
     *
     * @return TA
     */
    public static function TA(int $keyTag, int $algorithm, string $digest, int $digestType = DS::DIGEST_SHA1): TA
    {
        $ta = new TA();
        $ta->setKeyTag($keyTag);
        $ta->setAlgorithm($algorithm);
        $ta->setDigest($digest);
        $ta->setDigestType($digestType);

        return $ta;
    }

    /**
     * @param string    $algorithm
     * @param \DateTime $inception
     * @param \DateTime $expiration
     * @param int       $mode
     * @param int       $error
     * @param string    $keyData    binary string
     * @param string    $otherData  binary string
     *
     * @return TKEY
     */
    public static function TKEY(string $algorithm, \DateTime $inception, \DateTime $expiration, int $mode, int $error, string $keyData, string $otherData = ''): TKEY
    {
        $tkey = new TKEY();
        $tkey->setAlgorithm($algorithm);
        $tkey->setInception($inception);
        $tkey->setExpiration($expiration);
        $tkey->setMode($mode);
        $tkey->setError($error);
        $tkey->setKeyData($keyData);
        $tkey->setOtherData($otherData);

        return $tkey;
    }

    /**
     * @param int    $certificateUsage
     * @param int    $selector
     * @param int    $matchingType
     * @param string $certificateAssociationData
     *
     * @return TLSA
     */
    public static function TLSA(int $certificateUsage, int $selector, int $matchingType, string $certificateAssociationData): TLSA
    {
        $tlsa = new TLSA();
        $tlsa->setCertificateUsage($certificateUsage);
        $tlsa->setSelector($selector);
        $tlsa->setMatchingType($matchingType);
        $tlsa->setCertificateAssociationData($certificateAssociationData);

        return $tlsa;
    }

    /**
     * @param string    $algorithmName
     * @param \DateTime $timeSigned
     * @param int       $fudge
     * @param string    $mac
     * @param int       $originalId
     * @param int       $error
     * @param string    $otherData
     *
     * @return TSIG
     */
    public static function TSIG(string $algorithmName, \DateTime $timeSigned, int $fudge, string $mac, int $originalId, int $error, string $otherData): TSIG
    {
        $tsig = new TSIG();
        $tsig->setAlgorithmName($algorithmName);
        $tsig->setTimeSigned($timeSigned);
        $tsig->setFudge($fudge);
        $tsig->setMac($mac);
        $tsig->setOriginalId($originalId);
        $tsig->setError($error);
        $tsig->setOtherData($otherData);

        return $tsig;
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
