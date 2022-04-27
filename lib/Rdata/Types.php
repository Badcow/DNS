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

class Types
{
    /**
     * a host address.
     */
    public const A = 1;

    /**
     * an authoritative name server.
     */
    public const NS = 2;

    /**
     * a mail destination (OBSOLETE - use MX).
     */
    public const MD = 3;

    /**
     * a mail forwarder (OBSOLETE - use MX).
     */
    public const MF = 4;

    /**
     * the canonical name for an alias.
     */
    public const CNAME = 5;

    /**
     * marks the start of a zone of authority.
     */
    public const SOA = 6;

    /**
     * a mailbox domain name (EXPERIMENTAL).
     */
    public const MB = 7;

    /**
     * a mail group member (EXPERIMENTAL).
     */
    public const MG = 8;

    /**
     * a mail rename domain name (EXPERIMENTAL).
     */
    public const MR = 9;

    /**
     * a null RR (EXPERIMENTAL).
     */
    public const NULL = 10;

    /**
     * a well known service description.
     */
    public const WKS = 11;

    /**
     * a domain name pointer.
     */
    public const PTR = 12;

    /**
     * host information.
     */
    public const HINFO = 13;

    /**
     * mailbox or mail list information.
     */
    public const MINFO = 14;

    /**
     * mail exchange.
     */
    public const MX = 15;

    /**
     * text strings.
     */
    public const TXT = 16;

    /**
     * for Responsible Person.
     */
    public const RP = 17;

    /**
     * for AFS Data Base location.
     */
    public const AFSDB = 18;

    /**
     * for X.25 PSDN address.
     */
    public const X25 = 19;

    /**
     * for ISDN address.
     */
    public const ISDN = 20;

    /**
     * for Route Through.
     */
    public const RT = 21;

    /**
     * for NSAP address, NSAP style A record.
     */
    public const NSAP = 22;

    /**
     * for domain name pointer, NSAP style.
     */
    public const NSAP_PTR = 23;

    /**
     * for security signature.
     */
    public const SIG = 24;

    /**
     * for security key.
     */
    public const KEY = 25;

    /**
     * X.400 mail mapping information.
     */
    public const PX = 26;

    /**
     * Geographical Position.
     */
    public const GPOS = 27;

    /**
     * IP6 Address.
     */
    public const AAAA = 28;

    /**
     * Location Information.
     */
    public const LOC = 29;

    /**
     * Next Domain (OBSOLETE).
     */
    public const NXT = 30;

    /**
     * Endpoint Identifier.
     */
    public const EID = 31;

    /**
     * Nimrod Locator.
     */
    public const NIMLOC = 32;

    /**
     * Server Selection.
     */
    public const SRV = 33;

    /**
     * ATM Address.
     */
    public const ATMA = 34;

    /**
     * Naming Authority Pointer.
     */
    public const NAPTR = 35;

    /**
     * Key Exchanger.
     */
    public const KX = 36;

    /**
     * CERT.
     */
    public const CERT = 37;

    /**
     * A6 (OBSOLETE - use AAAA).
     */
    public const A6 = 38;

    /**
     * DNAME.
     */
    public const DNAME = 39;

    /**
     * SINK.
     */
    public const SINK = 40;

    /**
     * OPT.
     */
    public const OPT = 41;

    /**
     * APL.
     */
    public const APL = 42;

    /**
     * Delegation Signer.
     */
    public const DS = 43;

    /**
     * SSH Key Fingerprint.
     */
    public const SSHFP = 44;

    /**
     * IPSECKEY.
     */
    public const IPSECKEY = 45;

    /**
     * RRSIG.
     */
    public const RRSIG = 46;

    /**
     * NSEC.
     */
    public const NSEC = 47;

    /**
     * DNSKEY.
     */
    public const DNSKEY = 48;

    /**
     * DHCID.
     */
    public const DHCID = 49;

    /**
     * NSEC3.
     */
    public const NSEC3 = 50;

    /**
     * NSEC3PARAM.
     */
    public const NSEC3PARAM = 51;

    /**
     * TLSA.
     */
    public const TLSA = 52;

    /**
     * S/MIME cert association.
     */
    public const SMIMEA = 53;

    /**
     * Host Identity Protocol.
     */
    public const HIP = 55;

    /**
     * NINFO.
     */
    public const NINFO = 56;

    /**
     * RKEY.
     */
    public const RKEY = 57;

    /**
     * Trust Anchor LINK.
     */
    public const TALINK = 58;

    /**
     * Child DS.
     */
    public const CDS = 59;

    /**
     * DNSKEY(s) the Child wants reflected in DS.
     */
    public const CDNSKEY = 60;

    /**
     * OpenPGP Key.
     */
    public const OPENPGPKEY = 61;

    /**
     * Child-To-Parent Synchronization.
     */
    public const CSYNC = 62;

    /**
     * message digest for DNS zone.
     */
    public const ZONEMD = 63;

    public const SPF = 99;

    public const UINFO = 100;

    public const UID = 101;

    public const GID = 102;

    public const UNSPEC = 103;

    public const NID = 104;

    public const L32 = 105;

    public const L64 = 106;

    public const LP = 107;

    /**
     * an EUI-48 address.
     */
    public const EUI48 = 108;

    /**
     * an EUI-64 address.
     */
    public const EUI64 = 109;

    /**
     * Transaction Key.
     */
    public const TKEY = 249;

    /**
     * Transaction Signature.
     */
    public const TSIG = 250;

    /**
     * incremental transfer.
     */
    public const IXFR = 251;

    /**
     * transfer of an entire zone.
     */
    public const AXFR = 252;

    /**
     * mailbox-related RRs (MB, MG or MR).
     */
    public const MAILB = 253;

    /**
     * mail agent RRs (OBSOLETE - see MX).
     */
    public const MAILA = 254;

    /**
     * A request for some or all records the server has available.
     */
    public const ANY = 255;

    /**
     * URI.
     */
    public const URI = 256;

    /**
     * Certification Authority Restriction.
     */
    public const CAA = 257;

    /**
     * Application Visibility and Control.
     */
    public const AVC = 258;

    /**
     * Digital Object Architecture.
     */
    public const DOA = 259;

    /**
     * Automatic Multicast Tunneling Relay.
     */
    public const AMTRELAY = 260;

    /**
     * DNSSEC Trust Authorities.
     */
    public const TA = 32768;

    /**
     * DNSSEC Lookaside Validation (OBSOLETE).
     */
    public const DLV = 32769;

    public const RESERVED = 65535;

    public const TYPE_NAMES = [
        self::A => 'A',
        self::NS => 'NS',
        self::MD => 'MD',
        self::MF => 'MF',
        self::CNAME => 'CNAME',
        self::SOA => 'SOA',
        self::MB => 'MB',
        self::MG => 'MG',
        self::MR => 'MR',
        self::NULL => 'NULL',
        self::WKS => 'WKS',
        self::PTR => 'PTR',
        self::HINFO => 'HINFO',
        self::MINFO => 'MINFO',
        self::MX => 'MX',
        self::TXT => 'TXT',
        self::RP => 'RP',
        self::AFSDB => 'AFSDB',
        self::X25 => 'X25',
        self::ISDN => 'ISDN',
        self::RT => 'RT',
        self::NSAP => 'NSAP',
        self::NSAP_PTR => 'NSAP_PTR',
        self::SIG => 'SIG',
        self::KEY => 'KEY',
        self::PX => 'PX',
        self::GPOS => 'GPOS',
        self::AAAA => 'AAAA',
        self::LOC => 'LOC',
        self::NXT => 'NXT',
        self::EID => 'EID',
        self::NIMLOC => 'NIMLOC',
        self::SRV => 'SRV',
        self::ATMA => 'ATMA',
        self::NAPTR => 'NAPTR',
        self::KX => 'KX',
        self::CERT => 'CERT',
        self::A6 => 'A6',
        self::DNAME => 'DNAME',
        self::SINK => 'SINK',
        self::OPT => 'OPT',
        self::APL => 'APL',
        self::DS => 'DS',
        self::SSHFP => 'SSHFP',
        self::IPSECKEY => 'IPSECKEY',
        self::RRSIG => 'RRSIG',
        self::NSEC => 'NSEC',
        self::DNSKEY => 'DNSKEY',
        self::DHCID => 'DHCID',
        self::NSEC3 => 'NSEC3',
        self::NSEC3PARAM => 'NSEC3PARAM',
        self::TLSA => 'TLSA',
        self::SMIMEA => 'SMIMEA',
        self::HIP => 'HIP',
        self::NINFO => 'NINFO',
        self::RKEY => 'RKEY',
        self::TALINK => 'TALINK',
        self::CDS => 'CDS',
        self::CDNSKEY => 'CDNSKEY',
        self::OPENPGPKEY => 'OPENPGPKEY',
        self::CSYNC => 'CSYNC',
        self::ZONEMD => 'ZONEMD',
        self::SPF => 'SPF',
        self::UINFO => 'UINFO',
        self::UID => 'UID',
        self::GID => 'GID',
        self::UNSPEC => 'UNSPEC',
        self::NID => 'NID',
        self::L32 => 'L32',
        self::L64 => 'L64',
        self::LP => 'LP',
        self::EUI48 => 'EUI48',
        self::EUI64 => 'EUI64',
        self::TKEY => 'TKEY',
        self::TSIG => 'TSIG',
        self::IXFR => 'IXFR',
        self::AXFR => 'AXFR',
        self::MAILB => 'MAILB',
        self::MAILA => 'MAILA',
        self::ANY => 'ANY',
        self::URI => 'URI',
        self::CAA => 'CAA',
        self::AVC => 'AVC',
        self::DOA => 'DOA',
        self::AMTRELAY => 'AMTRELAY',
        self::TA => 'TA',
        self::DLV => 'DLV',
        self::RESERVED => 'RESERVED',
    ];

    /**
     * @param int|string $type
     */
    public static function isValid($type): bool
    {
        if (is_int($type)) {
            return array_key_exists($type, self::TYPE_NAMES);
        }

        return in_array($type, self::TYPE_NAMES) || 1 === preg_match('/^TYPE\d+$/', $type);
    }

    /**
     * Get the name of an RDATA type. E.g. RecordTypeEnum::getName(6) return 'SOA'.
     *
     * @param int $type The index of the type
     *
     * @throws UnsupportedTypeException
     */
    public static function getName(int $type): string
    {
        if (!self::isValid($type)) {
            throw new UnsupportedTypeException(sprintf('The integer "%d" does not correspond to a supported type.', $type));
        }

        return self::TYPE_NAMES[$type];
    }

    /**
     * Return the integer value of an RDATA type. E.g. getTypeFromName('MX') returns 15.
     *
     * @param string $name The name of the record type, e.g. = 'A' or 'MX' or 'SOA'
     *
     * @throws UnsupportedTypeException
     */
    public static function getTypeCode(string $name): int
    {
        $name = strtoupper(trim($name));

        if (false !== $type = array_search($name, self::TYPE_NAMES)) {
            return (int) $type;
        }

        if (1 === preg_match('/^TYPE(\d+)$/', $name, $matches)) {
            return (int) $matches[1];
        }

        throw new UnsupportedTypeException(sprintf('RData type "%s" is not supported.', $name));
    }
}
