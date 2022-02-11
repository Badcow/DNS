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

namespace Badcow\DNS\Edns\Option;

class Codes
{
    public const LLQ = 1;
    public const UL = 2;
    public const NSID = 3;
    public const DAU = 5;
    public const DHU = 6;
    public const N3U = 7;
    public const CLIENT_SUBNET = 8;
    public const EXPIRE = 9;
    public const COOKIE = 10;
    public const TCP_KEEPALIVE = 11;
    public const PADDING = 12;
    public const CHAIN = 13;
    public const KEY_CHAIN = 14;
    public const DNS_ERROR = 15;
    public const CLIENT_TAG = 16;
    public const SERVER_TAG = 17;

    /**
     * @var array
     */
    public static $names = [
        self::LLQ => 'LLQ',
        self::UL => 'UL',
        self::NSID => 'NSID',
        self::DAU => 'DAU',
        self::DHU => 'DHU',
        self::N3U => 'N3U',
        self::CLIENT_SUBNET => 'CLIENT_SUBNET',
        self::EXPIRE => 'EXPIRE',
        self::COOKIE => 'COOKIE',
        self::TCP_KEEPALIVE => 'TCP_KEEPALIVE',
        self::PADDING => 'PADDING',
        self::CHAIN => 'CHAIN',
        self::KEY_CHAIN => 'KEY_CHAIN',
        self::DNS_ERROR => 'DNS_ERROR',
        self::CLIENT_TAG => 'CLIENT_TAG',
        self::SERVER_TAG => 'SERVER_TAG',
    ];

    /**
     * @param int|string $option either the option name (string) or the option code (integer)
     */
    public static function isValid($option): bool
    {
        if (is_int($option)) {
            return array_key_exists($option, self::$names);
        }

        return in_array($option, self::$names);
    }

    /**
     * Get the name of an Option code. E.g. Codes::getName(8) return 'CLIENT_SUBNET'.
     *
     * @param int $code The index of the code
     *
     * @throws UnsupportedOptionException
     */
    public static function getName(int $code): string
    {
        if (!self::isValid($code)) {
            throw new UnsupportedOptionException(sprintf('The integer "%d" does not correspond to a supported code.', $code));
        }

        return self::$names[$code];
    }
}
