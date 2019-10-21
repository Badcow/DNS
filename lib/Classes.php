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

class Classes
{
    const INTERNET = 'IN';
    const CSNET = 'CS';
    const CHAOS = 'CH';
    const HESIOD = 'HS';

    /**
     * @var array
     */
    public static $classes = [
        self::CHAOS => 'CHAOS',
        self::CSNET => 'CSNET',
        self::HESIOD => 'Hesiod',
        self::INTERNET => 'Internet',
    ];

    const CLASS_IDS = [
        self::CHAOS => 3,
        self::CSNET => 2,
        self::HESIOD => 4,
        self::INTERNET => 1,
    ];

    /**
     * Determine if a class is valid.
     *
     * @param string $class
     *
     * @return bool
     */
    public static function isValid(string $class): bool
    {
        return array_key_exists($class, self::$classes);
    }

    /**
     * @param string $className
     * @return int
     * @throws \InvalidArgumentException
     */
    public static function getClassId(string $className): int
    {
        if (!self::isValid($className)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" is not a valid DNS class.', $className));
        }

        return self::CLASS_IDS[$className];
    }
}
