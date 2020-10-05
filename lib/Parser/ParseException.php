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

class ParseException extends \Exception
{
    /**
     * @var StringIterator
     */
    private $stringIterator;

    /**
     * ParseException constructor.
     */
    public function __construct(string $message = '', StringIterator $stringIterator = null, \Throwable $previous = null)
    {
        if (null !== $stringIterator) {
            $this->stringIterator = $stringIterator;
            $message .= sprintf(' [Line no: %d]', $this->getLineNumber());
        }

        parent::__construct($message, 0, $previous);
    }

    /**
     * Get line number of current entry on the StringIterator.
     */
    private function getLineNumber(): int
    {
        $pos = $this->stringIterator->key();
        $this->stringIterator->rewind();
        $lineNo = 1;

        while ($this->stringIterator->key() < $pos) {
            if ($this->stringIterator->is(Tokens::LINE_FEED)) {
                ++$lineNo;
            }
            $this->stringIterator->next();
        }

        return $lineNo;
    }
}
