<?php

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
     *
     * @param string $string
     */
    public function __construct(string $string = '')
    {
        parent::__construct(str_split($string));
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    public function is(string $value): bool
    {
        return $value === $this->current();
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    public function isNot(string $value): bool
    {
        return $value !== $this->current();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return implode($this->getArrayCopy());
    }
}
