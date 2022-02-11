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

class Factory
{
    /**
     * Creates a new Option object from a name.
     *
     * @throws UnsupportedOptionException
     */
    public static function newOptionFromName(string $name): OptionInterface
    {
        if (!self::isOptionImplemented($name)) {
            throw new UnsupportedOptionException($name);
        }

        $className = self::getOptionClassName($name);
        $optionInterface = new $className();
        if (!$optionInterface instanceof OptionInterface) {
            throw new \UnexpectedValueException(sprintf('Badcow\DNS\Edns\Option expected; "%s" instantiated.', gettype($optionInterface)));
        }

        return $optionInterface;
    }

    /**
     * @throws UnsupportedOptionException
     */
    public static function newOptionFromId(int $id): OptionInterface
    {
        return self::newOptionFromName(Codes::getName($id));
    }

    public static function isOptionImplemented(string $name): bool
    {
        return class_exists(self::getOptionClassName($name));
    }

    public static function isOptionCodeImplemented(int $optionCode): bool
    {
        try {
            return self::isOptionImplemented(Codes::getName($optionCode));
        } catch (UnsupportedOptionException $e) {
            return false;
        }
    }

    /**
     * Get the fully qualified class name of the Option class for $option.
     */
    public static function getOptionClassName(string $option): string
    {
        return __NAMESPACE__.'\\'.strtoupper($option);
    }
}
