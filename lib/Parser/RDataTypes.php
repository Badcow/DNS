<?php

/*
 * This file is part of Badcow DNS Library.
 *
 * (c) Samuel Williams <sam@badcow.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Badcow\DNS\Parser;

class RDataTypes
{
    /**
     * @var array
     */
    public static $names = [
        self::TYPE_A,
        self::TYPE_NS,
        self::TYPE_CNAME,
        self::TYPE_SOA,
        self::TYPE_PTR,
        self::TYPE_MX,
        self::TYPE_TXT,
        self::TYPE_AAAA,
        self::TYPE_OPT,
        self::TYPE_AXFR,
        self::TYPE_ANY,
        self::TYPE_AFSDB,
        self::TYPE_APL,
        self::TYPE_CAA,
        self::TYPE_CDNSKEY,
        self::TYPE_CDS,
        self::TYPE_CERT,
        self::TYPE_DHCID,
        self::TYPE_DLV,
        self::TYPE_DNSKEY,
        self::TYPE_DS,
        self::TYPE_IPSECKEY,
        self::TYPE_KEY,
        self::TYPE_KX,
        self::TYPE_LOC,
        self::TYPE_NAPTR,
        self::TYPE_NSEC,
        self::TYPE_NSEC3,
        self::TYPE_NSEC3PARAM,
        self::TYPE_RRSIG,
        self::TYPE_RP,
        self::TYPE_SIG,
        self::TYPE_SRV,
        self::TYPE_SSHFP,
        self::TYPE_TA,
        self::TYPE_TKEY,
        self::TYPE_TLSA,
        self::TYPE_TSIG,
        self::TYPE_URI,
        self::TYPE_DNAME,
    ];

    public const TYPE_A = 'A';
    public const TYPE_NS = 'NS';
    public const TYPE_CNAME = 'CNAME';
    public const TYPE_SOA = 'SOA';
    public const TYPE_PTR = 'PTR';
    public const TYPE_MX = 'MX';
    public const TYPE_TXT = 'TXT';
    public const TYPE_AAAA = 'AAAA';
    public const TYPE_OPT = 'OPT';
    public const TYPE_AXFR = 'AXFR';
    public const TYPE_ANY = 'ANY';
    public const TYPE_AFSDB = 'AFSDB';
    public const TYPE_APL = 'APL';
    public const TYPE_CAA = 'CAA';
    public const TYPE_CDNSKEY = 'CDNSKEY';
    public const TYPE_CDS = 'CDS';
    public const TYPE_CERT = 'CERT';
    public const TYPE_DHCID = 'DHCID';
    public const TYPE_DLV = 'DLV';
    public const TYPE_DNSKEY = 'DNSKEY';
    public const TYPE_DS = 'DS';
    public const TYPE_IPSECKEY = 'IPSECKEY';
    public const TYPE_KEY = 'KEY';
    public const TYPE_KX = 'KX';
    public const TYPE_LOC = 'LOC';
    public const TYPE_NAPTR = 'NAPTR';
    public const TYPE_NSEC = 'NSEC';
    public const TYPE_NSEC3 = 'NSEC3';
    public const TYPE_NSEC3PARAM = 'NSEC3PARAM';
    public const TYPE_RRSIG = 'RRSIG';
    public const TYPE_RP = 'RP';
    public const TYPE_SIG = 'SIG';
    public const TYPE_SRV = 'SRV';
    public const TYPE_SSHFP = 'SSHFP';
    public const TYPE_TA = 'TA';
    public const TYPE_TKEY = 'TKEY';
    public const TYPE_TLSA = 'TLSA';
    public const TYPE_TSIG = 'TSIG';
    public const TYPE_URI = 'URI';
    public const TYPE_DNAME = 'DNAME';

    /**
     * @param string $type
     *
     * @return bool
     */
    public static function isValid(string $type): bool
    {
        return false !== array_search($type, self::$names);
    }
}
