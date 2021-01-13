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

class StringIterator extends \ArrayIterator
{
    /**
     * StringIterator constructor.
     */
    public function __construct(string $string = '')
    {
        parent::__construct(str_split($string));
    }

    /**
     * Test if current character is equal to a value, or (if $value is an array) is one of the values in the array.
     *
     * @param string|array $value test if current character is equal to, or is in, $value
     *
     * @return bool true if current character is, or is one of, the values
     */
    public function is($value): bool
    {
        if (is_array($value)) {
            return in_array($this->current(), $value);
        }

        return (string) $value === $this->current();
    }

    /**
     * Test if current character is not equal to a value, or (if $value is an array) is not any of the values in the array.
     *
     * @param string|array $value test if current character is not equal to, or is not any of, $value
     *
     * @return bool true if current character is not, or is not one of, the values
     */
    public function isNot($value): bool
    {
        return !$this->is($value);
    }

    public function getRemainingAsString(): string
    {
        $string = '';
        while ($this->valid()) {
            $string .= $this->current();
            $this->next();
        }

        return $string;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return implode('', $this->getArrayCopy());
    }
}
