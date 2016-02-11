<?php

/*
 * This file is part of Badcow DNS Library.
 *
 * (c) Samuel Williams <sam@badcow.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Badcow\DNS\Ip;

use Badcow\DNS\Validator;

class Toolbox
{
    /**
     * Expands an IPv6 address to its full, non-short hand representation.
     *
     * @param string $ip IPv6 address
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    public static function expandIpv6($ip)
    {
        if (!Validator::validateIpv6Address($ip)) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid IPv6 address.', $ip));
        }

        $hex = unpack('H*hex', inet_pton($ip));
        $ip = substr(preg_replace('/([A-f0-9]{4})/', '$1:', $hex['hex']), 0, -1);

        return $ip;
    }

    /**
     * This function will expand in incomplete IPv6 address.
     * An incomplete IPv6 address is of the form `2001:db8:ffff:abcd`
     * i.e. one where there is less than eight hextets.
     *
     * @param string $ip IPv6 address
     * @return string Expanded incomplete IPv6 address
     */
    public static function expandIncompleteIpv6($ip)
    {
        $parts = explode(':', $ip);
        foreach ($parts as $i => $part) {
            $parts[$i] = str_pad($part, 4, '0', STR_PAD_LEFT);
        }

        return implode(':', $parts);
    }

    /**
     * Takes a valid IPv6 address and contracts it
     * to its shorter version.
     *
     * E.g.: 2001:0000:0000:acad:0000:0000:0000:0001 -> 2001:0:0:acad::1
     *
     * Note: If there is more than one set of consecutive hextets, the function
     * will favour the larger of the sets. If both sets of zeroes are the same
     * the second will be favoured in the omission of zeroes.
     *
     * E.g.: 2001:0000:0000:ab80:2390:0000:0000:000a -> 2001:0:0:ab80:2390::a
     *
     * @param string $ip IPv6 address
     * @throws \InvalidArgumentException
     *
     * @return string Contracted IPv6 address
     */
    public static function contractIpv6($ip)
    {
        if (!Validator::validateIpv6Address($ip)) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid IPv6 address.', $ip));
        }

        $ip = self::expandIpv6($ip);
        $hextets = explode(':', $ip);
        $decimals = [];

        foreach ($hextets as $hextet) {
            $decimals[] = hexdec($hextet);
        }

        $zeroes = [];
        $flag = false;

        foreach ($decimals as $i => $n) {
            if (0 === $n) {
                if (true === $flag) {
                    $arg = end($zeroes);
                    $arg['n'] += 1;
                } else {
                    $flag = true;
                    $arg = ['i' => $i, 'n' => 1];
                }

                $zeroes[$arg['i']] = $arg;

                continue;
            }

            $flag = false;
        }

        $i_0 = -1;
        $n_0 = 0;

        if (count($zeroes) > 0) {
            $n = 0;
            foreach ($zeroes as $arg) {
                if ($arg['n'] >= $n) {
                    $i_0 = $arg['i'];
                    $n_0 = $arg['n'];
                }
            }
        }

        $ip = '';

        foreach ($decimals as $i => $decimal) {
            if ($i > $i_0 && $i < $i_0 + $n_0) {
                continue;
            }

            if ($i === $i_0) {
                $ip .= '::';
                continue;
            }

            $ip .= (string) dechex($decimal);

            $ip .= ($i < 7) ? ':' : '';
        }

        return preg_replace('/\:{3}/', '::', $ip);
    }

    /**
     * Creates a reverse IPv4 address.
     *
     * @param $ip
     *
     * @return string
     */
    public static function reverseIpv4($ip)
    {
        $parts = array_reverse(explode('.', $ip));

        $address = implode('.', $parts);
        $address .= '.in-addr.arpa.';

        return $address;
    }

    /**
     * Creates a reverse IPv6 address.
     *
     * @param string $ip
     *
     * @return string
     */
    public static function reverseIpv6($ip)
    {
        try {
            $ip = self::expandIpv6($ip);
        } catch (\InvalidArgumentException $e) {
            $ip = self::expandIncompleteIpv6($ip);
        }

        $ip = str_replace(':', '', $ip);
        $ip = strrev($ip);
        $address = implode('.', str_split($ip));
        $address .= '.ip6.arpa.';

        return $address;
    }
}
