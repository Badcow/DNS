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

namespace Badcow\DNS;

use Badcow\DNS\Rdata\CNAME;
use Badcow\DNS\Rdata\NS;
use Badcow\DNS\Rdata\SOA;

class Validator
{
    public const ZONE_OKAY = 0;

    public const ZONE_NO_SOA = 1;

    public const ZONE_TOO_MANY_SOA = 2;

    public const ZONE_NO_NS = 4;

    public const ZONE_NO_CLASS = 8;

    public const ZONE_TOO_MANY_CLASSES = 16;

    /**
     * Validate the string as a valid hostname in accordance with RFC 952 {@link https://tools.ietf.org/html/rfc952}
     * and RFC 1123 {@link https://tools.ietf.org/html/rfc1123}.
     */
    public static function hostName(string $name): bool
    {
        return (bool) filter_var($name, FILTER_VALIDATE_DOMAIN, [
            'flags' => FILTER_FLAG_HOSTNAME,
        ]);
    }

    /**
     * Validate the string is a Fully Qualified Domain Name.
     */
    public static function fullyQualifiedDomainName(string $name): bool
    {
        if ('.' === $name) {
            return true;
        }
        if ('.' !== substr($name, -1, 1)) {
            return false;
        }

        return self::hostName($name);
    }

    /**
     * Validate the name for a Resource Record. This is distinct from validating a hostname in that this function
     * will permit '@' and wildcards as well as underscores used in SRV records.
     */
    public static function resourceRecordName(string $name): bool
    {
        return strlen($name) < 254 &&
            (1 === preg_match('/(?:^(?:\*\.)?((?!-)[a-z0-9_\-]{1,63}(?<!-)\.?){1,127}$)|^@$|^\*$/i', $name));
    }

    /**
     * Validates an IPv4 Address.
     *
     * @static
     */
    public static function ipv4(string $address): bool
    {
        return (bool) filter_var($address, FILTER_VALIDATE_IP, [
            'flags' => FILTER_FLAG_IPV4,
        ]);
    }

    /**
     * Validates an IPv6 Address.
     *
     * @static
     */
    public static function ipv6(string $address): bool
    {
        return (bool) filter_var($address, FILTER_VALIDATE_IP, [
            'flags' => FILTER_FLAG_IPV6,
        ]);
    }

    /**
     * Validates an IPv4 or IPv6 address.
     *
     * @static
     */
    public static function ipAddress(string $address): bool
    {
        return (bool) filter_var($address, FILTER_VALIDATE_IP);
    }

    /**
     * Validates that the zone meets
     * RFC-1035 especially that:
     *   1) 5.2.1 All RRs in the file should be of the same class.
     *   2) 5.2.2 Exactly one SOA RR should be present at the top of the zone.
     *   3) There is at least one NS record.
     *
     * Return values are:
     *   - ZONE_NO_SOA
     *   - ZONE_TOO_MANY_SOA
     *   - ZONE_NO_NS
     *   - ZONE_NO_CLASS
     *   - ZONE_TOO_MANY_CLASSES
     *   - ZONE_OKAY
     *
     * You SHOULD compare these return values to the defined constants of this
     * class rather than against integers directly.
     */
    public static function zone(Zone $zone): int
    {
        $n_soa = self::countResourceRecords($zone, SOA::TYPE);
        $n_ns = self::countResourceRecords($zone, NS::TYPE);
        $n_class = self::countClasses($zone);

        $totalError = 0;

        $incrementError = function (bool $errorCondition, int $errorOrdinal) use (&$totalError): void {
            $totalError += $errorCondition ? $errorOrdinal : 0;
        };

        $incrementError($n_soa < 1, self::ZONE_NO_SOA);
        $incrementError($n_soa > 1, self::ZONE_TOO_MANY_SOA);
        $incrementError($n_ns < 1, self::ZONE_NO_NS);
        $incrementError($n_class < 1, self::ZONE_NO_CLASS);
        $incrementError($n_class > 1, self::ZONE_TOO_MANY_CLASSES);

        return $totalError;
    }

    /**
     * Counts the number of Resource Records of a particular type ($type) in a Zone.
     *
     * @param string $type The ResourceRecord type to be counted. If NULL, then the method will return
     *                     the number of records without RData.
     *
     * @return int the number of records to be counted
     */
    public static function countResourceRecords(Zone $zone, ?string $type = null): int
    {
        $n = 0;
        foreach ($zone as $rr) {
            $n += (int) ($type === $rr->getType());
        }

        return $n;
    }

    /**
     * Validates a reverse IPv4 address. Ensures that all octets are in the range [0-255].
     */
    public static function reverseIpv4(string $address): bool
    {
        $pattern = '/^((?:[0-9]+\.){1,4})in\-addr\.arpa\.$/i';

        if (1 !== preg_match($pattern, $address, $matches)) {
            return false;
        }

        $octets = explode('.', $matches[1]);
        array_pop($octets); //Remove the last decimal from the array

        foreach ($octets as $octet) {
            if ((int) $octet > 255) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validates a reverse IPv6 address.
     */
    public static function reverseIpv6(string $address): bool
    {
        $pattern = '/^(?:[0-9a-f]\.){1,32}ip6\.arpa\.$/i';

        return 1 === preg_match($pattern, $address);
    }

    /**
     * Determine the number of unique non-null classes in a Zone. In a valid zone this MUST be 1.
     */
    private static function countClasses(Zone $zone): int
    {
        $classes = [];

        foreach ($zone as $rr) {
            if (null !== $rr->getClass()) {
                $classes[$rr->getClass()] = null;
            }
        }

        return count($classes);
    }

    /**
     * Ensure $zone does not contain existing CNAME alias corresponding to $newRecord's name.
     *
     * E.g.
     *      www IN CNAME example.com.
     *      www IN TXT "This is a violation of DNS specifications."
     *
     * @see https://tools.ietf.org/html/rfc1034#section-3.6.2
     */
    public static function noAliasInZone(Zone $zone, ResourceRecord $newRecord): bool
    {
        foreach ($zone as $rr) {
            if (CNAME::TYPE === $rr->getType()
                && $newRecord->getName() === $rr->getName()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine if string is a base64 encoded string.
     *
     * @param string $string A base64 encoded string
     */
    public static function isBase64Encoded(string $string): bool
    {
        if (1 !== preg_match('/^[a-zA-Z0-9\/\r\n+ ]*={0,2}$/', $string)) {
            return false;
        }

        if (null === $string = preg_replace('/[^a-zA-Z0-9\/+=]/', '', $string)) {
            return false;
        }

        if (false === $decoded = base64_decode($string, true)) {
            return false;
        }

        return $string === base64_encode($decoded);
    }

    /**
     * Determine if string is a base32 encoded string.
     */
    public static function isBase32Encoded(string $string): bool
    {
        return 1 === preg_match('/^[A-Z2-7]+=*$/', $string);
    }

    /**
     * Determine if string is a base32hex (extended hex) encoded string.
     */
    public static function isBase32HexEncoded(string $string): bool
    {
        return 1 === preg_match('/^[a-zA-Z0-9]+=*$/', $string);
    }

    /**
     * Determine if string is a base16 encoded string.
     */
    public static function isBase16Encoded(string $string): bool
    {
        return 1 === preg_match('/^[0-9a-f]+$/i', $string);
    }

    /**
     * Determine if $integer is an unsigned integer less than 2^$numberOfBits.
     *
     * @param int $integer      The integer to test
     * @param int $numberOfBits The upper limit that the integer can be expressed as an exponent of 2
     */
    public static function isUnsignedInteger(int $integer, int $numberOfBits): bool
    {
        $maxBits = PHP_INT_SIZE * 8 - 1;

        if ($numberOfBits > $maxBits) {
            throw new \RuntimeException(sprintf('Number of bits "%d" exceeds maximum binary exponent of "%d".', $numberOfBits, $maxBits));
        }

        return (0 <= $integer) && ($integer < (2 ** $numberOfBits));
    }
}
