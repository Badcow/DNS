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

use Badcow\DNS\Rdata\NS;
use Badcow\DNS\Rdata\SOA;

class Validator
{
    const ZONE_OKAY = 0;

    const ZONE_NO_SOA = 1;

    const ZONE_TOO_MANY_SOA = 2;

    const ZONE_NO_NS = 4;

    const ZONE_NO_CLASS = 8;

    const ZONE_TOO_MANY_CLASSES = 16;

    /**
     * Validate the string as a valid hostname in accordance with RFC 952 {@link https://tools.ietf.org/html/rfc952}
     * and RFC 1123 {@link https://tools.ietf.org/html/rfc1123}.
     *
     * @param string $name
     *
     * @return bool
     */
    public static function hostName(string $name): bool
    {
        return self::fullyQualifiedDomainName(rtrim($name, '.').'.');
    }

    /**
     * Validate the string is a Fully Qualified Domain Name.
     *
     * @param string $name
     *
     * @return bool
     */
    public static function fullyQualifiedDomainName(string $name): bool
    {
        //Is there a trailing dot?
        if ('.' !== substr($name, -1, 1)) {
            return false;
        }

        if (strlen($name) > 255) {
            return false;
        }

        $labels = explode('.', rtrim($name, '.'));

        //Are there more than 127 levels?
        if (count($labels) > 127) {
            return false;
        }

        $isValid = true;
        foreach ($labels as $label) {
            //Does the label start with a hyphen?
            $isValid &= ('-' !== substr($label, 0, 1));

            //Does the label end with a hyphen?
            $isValid &= ('-' !== substr($label, -1, 1));

            //Does the label contain anything other than alphanumeric characters or hyphens?
            $isValid &= (1 === preg_match('/^[a-zA-Z0-9\-]+$/i', $label));

            //Is the length of the label between 1 and 63 characters?
            $isValid &= strlen($label) > 0 && strlen($label) < 64;
        }

        return $isValid;
    }

    /**
     * Validate the name for a Resource Record. This is distinct from validating a hostname in that this function
     * will permit '@' and wildcards as well as underscores used in SRV records.
     *
     * @param string $name
     *
     * @return bool
     */
    public static function resourceRecordName(string $name): bool
    {
        if ('@' === $name) {
            return true;
        }

        if ('*.' === $name) {
            return false;
        }

        if (strlen($name) > 255) {
            return false;
        }

        $labels = explode('.', rtrim($name, '.'));

        //Are there more than 127 levels?
        if (count($labels) > 127) {
            return false;
        }

        $isValid = true;
        foreach ($labels as $i => $label) {
            //Is the first label a wildcard?
            if (0 === $i && '*' === $label) {
                $isValid &= true;
                continue;
            }

            //Does the label start with a hyphen?
            $isValid &= ('-' !== substr($label, 0, 1));

            //Does the label end with a hyphen?
            $isValid &= ('-' !== substr($label, -1, 1));

            //Does the label contain anything other than alphanumeric characters, underscores or hyphens?
            $isValid &= (1 === preg_match('/^[a-zA-Z0-9\-_]+$/i', $label));

            //Is the length of the label between 1 and 63 characters?
            $isValid &= strlen($label) > 0 && strlen($label) < 64;
        }

        return $isValid;
    }

    /**
     * Validates an IPv4 Address.
     *
     * @static
     *
     * @param string $address
     *
     * @return bool
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
     *
     * @param string $address
     *
     * @return bool
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
     *
     * @param $address
     *
     * @return bool
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
     *
     * @param Zone $zone
     *
     * @return int
     */
    public static function zone(Zone $zone): int
    {
        $n_soa = self::countResourceRecords($zone, SOA::TYPE);
        $n_ns = self::countResourceRecords($zone, NS::TYPE);
        $classes = [];

        foreach ($zone as $rr) {
            if (null !== $rr->getClass()) {
                $classes[$rr->getClass()] = null;
            }
        }

        $n_class = count($classes);
        $errors = 0;

        if ($n_soa < 1) {
            $errors += self::ZONE_NO_SOA;
        }

        if ($n_soa > 1) {
            $errors += self::ZONE_TOO_MANY_SOA;
        }

        if ($n_ns < 1) {
            $errors += self::ZONE_NO_NS;
        }

        if ($n_class < 1) {
            $errors += self::ZONE_NO_CLASS;
        }

        if ($n_class > 1) {
            $errors += self::ZONE_TOO_MANY_CLASSES;
        }

        return $errors;
    }

    /**
     * Counts the number of Resource Records of a particular type ($type) in a Zone.
     *
     * @param Zone  $zone
     * @param string          $type The ResourceRecord type to be counted. If NULL, then the method will return
     *                            the number of records without RData.
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
     *
     * @param string $address
     *
     * @return bool
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
     *
     * @param string $address
     *
     * @return bool
     */
    public static function reverseIpv6(string $address): bool
    {
        $pattern = '/^(?:[0-9a-f]\.){1,32}ip6\.arpa\.$/i';

        return 1 === preg_match($pattern, $address);
    }
}
