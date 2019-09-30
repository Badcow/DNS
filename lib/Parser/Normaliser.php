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

class Normaliser
{
    /**
     * @var StringIterator
     */
    private $string;

    /**
     * @var string
     */
    private $normalisedString = '';

    /**
     * @var bool
     */
    private $includeComments;

    /**
     * @var string
     */
    private $comment = '';

    /**
     * Normaliser constructor.
     *
     * @param string $zone
     * @param bool   $includeComments
     */
    public function __construct(string $zone, bool $includeComments = false)
    {
        //Remove Windows line feeds and tabs
        $zone = str_replace([Tokens::CARRIAGE_RETURN, Tokens::TAB], ['', Tokens::SPACE], $zone);

        $this->string = new StringIterator($zone);
        $this->includeComments = $includeComments;
    }

    /**
     * @param string $zone
     * @param bool   $includeComments
     *
     * @return string
     *
     * @throws ParseException
     */
    public static function normalise(string $zone, bool $includeComments = false): string
    {
        return (new self($zone, $includeComments))->process();
    }

    /**
     * @return string
     *
     * @throws ParseException
     */
    public function process(): string
    {
        while ($this->string->valid()) {
            $this->handleTxt();
            $this->handleComment();
            $this->handleMultiline();
            $this->append();
        }

        $this->removeWhitespace();

        return $this->normalisedString;
    }

    /**
     * Parses the comments.
     */
    private function handleComment(): void
    {
        if ($this->string->isNot(Tokens::SEMICOLON)) {
            return;
        }

        $this->string->next();

        while ($this->string->isNot(Tokens::LINE_FEED) && $this->string->valid()) {
            if ($this->includeComments) {
                $this->comment .= $this->string->current();
            }
            $this->string->next();
        }
    }

    /**
     * Handle text inside of double quotations. When this function is called, the String pointer MUST be at the
     * double quotation mark.
     *
     * @throws ParseException
     */
    private function handleTxt(): void
    {
        if ($this->string->isNot(Tokens::DOUBLE_QUOTES)) {
            return;
        }

        $this->append();

        while ($this->string->isNot(Tokens::DOUBLE_QUOTES)) {
            if (!$this->string->valid()) {
                throw new ParseException('Unbalanced double quotation marks. End of file reached.');
            }

            //If escape character
            if ($this->string->is(Tokens::BACKSLASH)) {
                $this->append();
            }

            if ($this->string->is(Tokens::LINE_FEED)) {
                throw new ParseException('Line Feed found within double quotation marks context.', $this->string);
            }

            $this->append();
        }
    }

    /**
     * Move multi-line records onto single line.
     *
     * @throws ParseException
     */
    private function handleMultiline(): void
    {
        if ($this->string->isNot(Tokens::OPEN_BRACKET)) {
            return;
        }

        $this->string->next();
        while ($this->string->valid()) {
            $this->handleTxt();
            $this->handleComment();

            if ($this->string->is(Tokens::LINE_FEED)) {
                $this->string->next();
                continue;
            }

            if ($this->string->is(Tokens::CLOSE_BRACKET)) {
                $this->string->next();

                return;
            }

            $this->append();
        }

        throw new ParseException('End of file reached. Unclosed bracket.');
    }

    /**
     * Remove superfluous whitespace characters from string.
     *
     * @throws \UnexpectedValueException
     */
    private function removeWhitespace(): void
    {
        $string = preg_replace('/ {2,}/', Tokens::SPACE, $this->normalisedString);

        if (!is_string($string)) {
            throw new \UnexpectedValueException('Unexpected value returned from \preg_replace()/.');
        }

        $lines = [];

        foreach (explode(Tokens::LINE_FEED, $string) as $line) {
            if ('' !== $line = trim($line)) {
                $lines[] = $line;
            }
        }
        $this->normalisedString = implode(Tokens::LINE_FEED, $lines);
    }

    /**
     * Add current entry to normalisedString and moves iterator to next entry.
     */
    private function append()
    {
        if ($this->string->is(Tokens::LINE_FEED) && $this->includeComments && '' !== $this->comment) {
            $this->normalisedString = rtrim($this->normalisedString, ' ').Tokens::SEMICOLON.$this->comment;
            $this->comment = '';
        }

        $this->normalisedString .= $this->string->current();
        $this->string->next();
    }
}
