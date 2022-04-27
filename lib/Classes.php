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
    public const INTERNET = 'IN';
    public const CSNET = 'CS';
    public const CHAOS = 'CH';
    public const HESIOD = 'HS';

    /**
     * @var array
     */
    public static $classes = [
        self::CHAOS => 'CHAOS',
        self::CSNET => 'CSNET',
        self::HESIOD => 'Hesiod',
        self::INTERNET => 'Internet',
    ];

    public const CLASS_IDS = [
        self::CHAOS => 3,
        self::CSNET => 2,
        self::HESIOD => 4,
        self::INTERNET => 1,
    ];

    /**
     * @const string[]
     */
    public const IDS_CLASSES = [
        1 => 'IN',
        2 => 'CS',
        3 => 'CH',
        4 => 'HS',
    ];

    /**
     * Determine if a class is valid.
     */
    public static function isValid(string $class): bool
    {
        if (array_key_exists($class, self::$classes)) {
            return true;
        }

        return 1 === preg_match('/^CLASS\d+$/', $class);
    }

    /**
     * @throws \InvalidArgumentException
     */
    public static function getClassId(string $className): int
    {
        if (!self::isValid($className)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" is not a valid DNS class.', $className));
        }

        if (1 === preg_match('/^CLASS(\d+)$/', $className, $matches)) {
            return (int) $matches[1];
        }

        return self::CLASS_IDS[$className];
    }

    public static function getClassName(int $classId): string
    {
        if (array_key_exists($classId, self::IDS_CLASSES)) {
            return self::IDS_CLASSES[$classId];
        }

        return 'CLASS'.$classId;
    }
}
