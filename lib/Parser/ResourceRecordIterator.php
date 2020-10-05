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

class ResourceRecordIterator extends \ArrayIterator
{
    public function __construct(string $resourceRecord)
    {
        parent::__construct(explode(Tokens::SPACE, $resourceRecord));
    }

    /**
     * Return pointer to previous position.
     */
    public function prev(): void
    {
        if (!$this->valid()) {
            $this->end();

            return;
        }

        $this->seek((int) $this->key() - 1);
    }

    /**
     * Set pointer to the end of the array.
     */
    public function end(): void
    {
        $lastPos = $this->count() - 1;
        $this->seek($lastPos);
    }

    /**
     * Get all the remaining values of an iterator as an array. This will move the pointer to the end of the array.
     */
    public function getRemainingAsString(): string
    {
        $values = [];
        while ($this->valid()) {
            $values[] = $this->current();
            $this->next();
        }

        return implode(Tokens::SPACE, $values);
    }

    public function __toString(): string
    {
        return implode(Tokens::SPACE, $this->getArrayCopy());
    }
}
