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

namespace Badcow\DNS\Parser;

class TimeFormat
{
    public const TIME_FORMAT_REGEX = '/^(?:(?<w>\d+)w)?(?:(?<d>\d+)d)?(?:(?<h>\d+)h)?(?:(?<m>\d+)m)?(?:(?<s>\d+)s)?$/i';

    /**
     * Maximum time is the the lesser of 0xffffffff or the PHP maximum integer.
     *
     * @var int|null
     */
    public static $maxTime;

    public const TIME_MULTIPLIERS = [
        'w' => 604800,
        'd' => 86400,
        'h' => 3600,
        'm' => 60,
        's' => 1,
    ];

    /**
     * Check if given token looks like time format.
     *
     * @param string $value the time value to be evaluated
     *
     * @return bool true if $value is a valid time format
     */
    public static function isTimeFormat(string $value): bool
    {
        return is_numeric($value) || 1 === \preg_match(self::TIME_FORMAT_REGEX, $value);
    }

    /**
     * Convert human readable time format to seconds.
     *
     * @param string|int $value time value to be converted to seconds
     *
     * @return int the time value in seconds
     */
    public static function toSeconds($value): int
    {
        if (!isset(static::$maxTime)) {
            static::$maxTime = min(0xFFFFFFFF, PHP_INT_MAX);
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        if (1 === preg_match_all(self::TIME_FORMAT_REGEX, $value, $matches)) {
            $sec = (int) $matches['w'][0] * 604800 +
                   (int) $matches['d'][0] * 86400 +
                   (int) $matches['h'][0] * 3600 +
                   (int) $matches['m'][0] * 60 +
                   (int) $matches['s'][0];

            return $sec < static::$maxTime ? $sec : 0;
        }

        return 0;
    }

    /**
     * Convert number of seconds to human readable format.
     *
     * @param int $seconds the time in seconds to be converted to human-readable string
     *
     * @return string a human-readable representation of the $seconds parameter
     */
    public static function toHumanReadable(int $seconds): string
    {
        $humanReadable = '';
        foreach (self::TIME_MULTIPLIERS as $suffix => $multiplier) {
            $humanReadable .= ($t = floor($seconds / $multiplier)) > 0 ? $t.$suffix : '';
            $seconds -= $t * $multiplier;
        }

        return $humanReadable;
    }
}
