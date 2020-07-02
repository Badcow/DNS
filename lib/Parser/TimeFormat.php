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
    public const TIME_FORMAT_REGEX = '/^(?:(\d+)w)?(?:(\d+)d)?(?:(\d+)h)?(?:(\d+)m)?(?:(\d+)s)?$/i';

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
     * @param string $value
     *
     * @return bool
     */
    public static function isTimeFormat($value): bool
    {
        return \is_numeric($value) || 1 === \preg_match(self::TIME_FORMAT_REGEX, $value);
    }

    /**
     * Convert human readable time format to seconds.
     *
     * @param string $value
     *
     * @return int
     */
    public static function toSeconds($value): int
    {
        $seconds = 0;

        if (\is_numeric($value)) {
            $seconds = (int) $value;
        } elseif (1 === \preg_match(self::TIME_FORMAT_REGEX, $value, $matches)) {
            \array_shift($matches);
            $seconds = (int) \array_sum(\array_map(function ($fragment, $multiplier) {
                return (int) $fragment * $multiplier;
            }, $matches, self::TIME_MULTIPLIERS));
        }

        return $seconds < 2 ** 31 ? $seconds : 0;
    }

    /**
     * Convert number of seconds to human readable format.
     *
     * @param int $seconds
     *
     * @return string
     */
    public static function toHumanReadable(int $seconds): string
    {
        $humanReadable = '';
        foreach (self::TIME_MULTIPLIERS as $suffix => $multiplier) {
            if ($seconds < $multiplier) {
                continue;
            }
            $current = \floor($seconds / $multiplier);
            if ($current > 0) {
                $humanReadable .= \sprintf('%d%s', $current, $suffix);
                $seconds -= ($current * $multiplier);
            }
        }

        return $humanReadable;
    }
}
