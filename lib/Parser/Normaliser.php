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
     * @var int
     */
    private $commentOptions;

    /**
     * @var string
     */
    private $comment = '';

    /**
     * Comments that are within a multiline context, i.e. between brackets.
     *
     * @var string
     */
    private $multilineComments = '';

    /**
     * Normaliser constructor.
     */
    public function __construct(string $zone, int $commentOptions = Comments::NONE)
    {
        //Remove Windows line feeds and tabs
        $zone = str_replace([Tokens::CARRIAGE_RETURN, Tokens::TAB], ['', Tokens::SPACE], $zone);

        $this->string = new StringIterator($zone);
        $this->commentOptions = $commentOptions;
    }

    /**
     * @throws ParseException
     */
    public static function normalise(string $zone, int $includeComments = Comments::NONE): string
    {
        return (new self($zone, $includeComments))->process();
    }

    /**
     * @throws ParseException
     */
    public function process(): string
    {
        while ($this->string->valid()) {
            $this->handleTxt();
            $this->handleMultiline();
            $this->handleComment(Comments::END_OF_ENTRY | Comments::ORPHAN);
            $this->append();
        }

        $this->removeWhitespace();

        return $this->normalisedString;
    }

    /**
     * Parses the comments.
     *
     * @param int $condition
     */
    private function handleComment($condition = Comments::ALL): void
    {
        if ($this->string->isNot(Tokens::SEMICOLON)) {
            return;
        }

        $this->string->next();

        while ($this->string->isNot(Tokens::LINE_FEED) && $this->string->valid()) {
            if ($this->commentOptions & $condition) {
                if ($condition & Comments::MULTILINE) {
                    $this->multilineComments .= $this->string->current();
                } else {
                    $this->comment .= $this->string->current();
                }
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
            $this->handleComment(Comments::MULTILINE);

            if ($this->string->is(Tokens::LINE_FEED)) {
                $this->string->next();
                continue;
            }

            if ($this->string->is(Tokens::CLOSE_BRACKET)) {
                $this->string->next();

                $this->process();

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
        if (null === $string = preg_replace('/ {2,}/', Tokens::SPACE, $this->normalisedString)) {
            throw new \UnexpectedValueException('Unexpected value returned from \preg_replace().');
        }

        $lines = [];

        foreach (explode(Tokens::LINE_FEED, $string) as $line) {
            if ('' !== $line = rtrim($line)) {
                $lines[] = $line;
            }
        }
        $this->normalisedString = implode(Tokens::LINE_FEED, $lines);
    }

    /**
     * Add current entry to normalisedString and moves iterator to next entry.
     */
    private function append(): void
    {
        if (($this->string->is(Tokens::LINE_FEED) || !$this->string->valid()) &&
            $this->commentOptions &&
            ('' !== $this->comment || '' !== $this->multilineComments)) {
            $this->appendComment();
        }

        $this->normalisedString .= $this->string->current();
        $this->string->next();
    }

    private function appendComment(): void
    {
        $zone = rtrim($this->normalisedString, Tokens::SPACE);

        //If there is no Resource Record on the line
        if ((Tokens::LINE_FEED === substr($zone, -1, 1) || 0 === strlen($zone))) {
            if ($this->commentOptions & Comments::ORPHAN) {
                $this->normalisedString = sprintf('%s;%s', $zone, trim($this->comment));
            }
            $this->comment = '';
            $this->multilineComments = '';

            return;
        }

        $comments = '';

        if (($this->commentOptions & Comments::MULTILINE) && '' !== $this->multilineComments) {
            $comments .= $this->multilineComments;
        }

        if (($this->commentOptions & Comments::END_OF_ENTRY) && '' !== $this->comment) {
            $comments .= $this->comment;
        }

        if ('' !== $comments = trim($comments)) {
            $this->normalisedString = sprintf('%s;%s', $zone, $comments);
        }

        $this->comment = '';
        $this->multilineComments = '';
    }
}
