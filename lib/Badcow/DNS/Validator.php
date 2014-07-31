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

class Validator
{
    /**
     * @constant
     */
    const FQDN_PATTERN = '/(?:(?=^.{1,254}$)(^(?:(?!\d+\.)[a-zA-Z0-9_\-]{1,63}\.?)+(?:[a-zA-Z]{2,})\.$))|(?:^@$)/';

    /**
     * @constant
     */
    const UQDN_PATTERN = '/(?:(?=^.{1,254}$)(^(?:(?!\d+\.)[a-zA-Z0-9_\-]{1,63}\.?)+(?:[a-zA-Z]{2,})))|(?:^@$)/';

    /**
     * @param string $string
     * @param bool $trailingDot
     * @return bool
     */
    public static function validateFqdn($string, $trailingDot = true)
    {
        if ($trailingDot) {
            return preg_match(self::FQDN_PATTERN, $string) === 1;
        }

        return preg_match(self::UQDN_PATTERN, $string) === 1;
    }

    /**
     * Validates an IPv4 Address
     *
     * @static
     * @param string $ipAddress
     * @return bool
     */
    public static function validateIpv4Address($ipAddress)
    {
        return (bool) filter_var($ipAddress, FILTER_VALIDATE_IP, array(
            'flags' => FILTER_FLAG_IPV4,
        ));
    }

    /**
     * Validates an IPv6 Address
     *
     * @static
     * @param string $ipAddress
     * @return bool
     */
    public static function validateIpv6Address($ipAddress)
    {
        return (bool) filter_var($ipAddress, FILTER_VALIDATE_IP, array(
            'flags' => FILTER_FLAG_IPV6,
        ));
    }

    /**
     * Validates an IPv4 or IPv6 address
     *
     * @static
     * @param $ipAddress
     * @return bool
     */
    public static function validateIpAddress($ipAddress)
    {
        return (bool) filter_var($ipAddress, FILTER_VALIDATE_IP);
    }

    /**
     * Validates a zone file
     *
     * @param string $zonename
     * @param string $directory
     * @param string $named_checkzonePath
     * @return bool
     */
    public static function validateZoneFile($zonename, $directory, $named_checkzonePath = 'named-checkzone')
    {
        $command = sprintf('%s -q %s %s', $named_checkzonePath, $zonename, $directory);
        exec($command, $output, $exit_status);

        return $exit_status === 0;
    }
}