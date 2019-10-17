<?php

namespace Badcow\DNS\Rdata;

class TypeCodes
{
    /**
     * @var array
     */
    const TYPE_NAMES = [
        self::A => 'A',
        self::NS => 'NS',
        self::CNAME => 'CNAME',
        self::SOA => 'SOA',
        self::PTR => 'PTR',
        self::MX => 'MX',
        self::TXT => 'TXT',
        self::AAAA => 'AAAA',
        self::OPT => 'OPT',
        self::AXFR => 'AXFR',
        self::ANY => 'ANY',
        self::AFSDB => 'AFSDB',
        self::APL => 'APL',
        self::CAA => 'CAA',
        self::CDNSKEY => 'CDNSKEY',
        self::CDS => 'CDS',
        self::CERT => 'CERT',
        self::DHCID => 'DHCID',
        self::DLV => 'DLV',
        self::DNSKEY => 'DNSKEY',
        self::DS => 'DS',
        self::IPSECKEY => 'IPSECKEY',
        self::KEY => 'KEY',
        self::KX => 'KX',
        self::LOC => 'LOC',
        self::NAPTR => 'NAPTR',
        self::NSEC => 'NSEC',
        self::NSEC3 => 'NSEC3',
        self::NSEC3PARAM => 'NSEC3PARAM',
        self::RRSIG => 'RRSIG',
        self::RP => 'RP',
        self::SIG => 'SIG',
        self::SRV => 'SRV',
        self::SSHFP => 'SSHFP',
        self::TA => 'TA',
        self::TKEY => 'TKEY',
        self::TLSA => 'TLSA',
        self::TSIG => 'TSIG',
        self::URI => 'URI',
        self::DNAME => 'DNAME',
    ];

    public const A = 1;
    public const NS = 2;
    public const CNAME = 5;
    public const SOA = 6;
    public const PTR = 12;
    public const MX = 15;
    public const TXT = 16;
    public const AAAA = 28;
    public const OPT = 41;
    public const AXFR = 252;
    public const ANY = 255;
    public const AFSDB = 18;
    public const APL = 42;
    public const CAA = 257;
    public const CDNSKEY = 60;
    public const CDS = 59;
    public const CERT = 37;
    public const DHCID = 49;
    public const DLV = 32769;
    public const DNSKEY = 48;
    public const DS = 43;
    public const IPSECKEY = 45;
    public const KEY = 25;
    public const KX = 36;
    public const LOC = 29;
    public const NAPTR = 35;
    public const NSEC = 47;
    public const NSEC3 = 50;
    public const NSEC3PARAM = 51;
    public const RRSIG = 46;
    public const RP = 17;
    public const SIG = 24;
    public const SRV = 33;
    public const SSHFP = 44;
    public const TA = 32768;
    public const TKEY = 249;
    public const TLSA = 52;
    public const TSIG = 250;
    public const URI = 256;
    public const DNAME = 39;

    /**
     * @param int|string $type
     *
     * @return bool
     */
    public static function isValid($type): bool
    {
        return array_key_exists($type, self::TYPE_NAMES) || in_array($type, self::TYPE_NAMES);
    }

    /**
     * Get the name of an RDATA type. E.g. RecordTypeEnum::getName(6) return 'SOA'.
     *
     * @param int $type The index of the type
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public static function getName(int $type): string
    {
        if (!self::isValid($type)) {
            throw new \InvalidArgumentException(sprintf('The integer "%d" does not correspond to a valid type', $type));
        }

        return self::TYPE_NAMES[$type];
    }

    /**
     * Return the integer value of an RDATA type. E.g. getTypeFromName('MX') returns 15.
     *
     * @param string $name The name of the record type, e.g. = 'A' or 'MX' or 'SOA'
     *
     * @return int
     *
     * @throws \InvalidArgumentException
     */
    public static function getTypeCode(string $name): int
    {
        $type = array_search(strtoupper(trim($name)), self::TYPE_NAMES);
        if (false === $type || !is_int($type)) {
            throw new \InvalidArgumentException(sprintf('RData type "%s" is not defined.', $name));
        }

        return $type;
    }
}