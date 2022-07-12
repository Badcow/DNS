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

namespace Badcow\DNS\Rdata;

use Badcow\DNS\Parser\StringIterator;
use Badcow\DNS\Parser\Tokens;

/**
 * @see https://tools.ietf.org/html/rfc1035#section-3.3.14
 */
class TXT implements RdataInterface
{
    use RdataTrait;

    public const TYPE = 'TXT';
    public const TYPE_CODE = 16;

    public const WHITESPACE = [Tokens::SPACE, Tokens::TAB, Tokens::LINE_FEED, Tokens::CARRIAGE_RETURN];

    /**
     * @var string|null
     */
    private $text;

    public function setText(?string $text, bool $stripped = false): void
    {
        if (null === $text) {
            $this->text = null;

            return;
        }

        $this->text = $stripped ? $text : stripslashes($text);
    }

    public function getText(): string
    {
        return $this->text ?? '';
    }

    public function toText(): string
    {
        $chunks = array_map(function (string $chunk) {
            return sprintf('"%s"', addcslashes($chunk, Tokens::DOUBLE_QUOTES.Tokens::BACKSLASH));
        }, str_split($this->text ?? '', 255));

        return implode(' ', $chunks);
    }

    public function toWire(): string
    {
        return $this->text ?? '';
    }

    public function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): void
    {
        $rdLength = $rdLength ?? strlen($rdata);
        $this->setText(substr($rdata, $offset, $rdLength));
        $offset += $rdLength;
    }

    public function fromText(string $text): void
    {
        $string = new StringIterator($text);
        $txt = new StringIterator();
        $whitespace = [Tokens::SPACE, Tokens::TAB];

        while ($string->valid()) {
            if ($string->is(static::WHITESPACE)) {
                $string->next();
                continue;
            }

            if ($string->is(Tokens::DOUBLE_QUOTES)) {
                self::handleTxt($string, $txt);
                $string->next();
                continue;
            }

            self::handleContiguousString($string, $txt);
            break;
        }

        $this->setText((string) $txt, true);
    }

    /**
     * This handles the case where character string is encapsulated inside quotation marks.
     *
     * @param StringIterator $string The string to parse
     * @param StringIterator $txt    The output string
     */
    public static function handleTxt(StringIterator $string, StringIterator $txt): void
    {
        if ($string->isNot(Tokens::DOUBLE_QUOTES)) {
            return;
        }

        $string->next();

        while ($string->isNot(Tokens::DOUBLE_QUOTES) && $string->valid()) {
            if ($string->is(Tokens::BACKSLASH)) {
                $string->next();
            }

            $txt->append($string->current());
            $string->next();
        }
    }

    /**
     * This handles the case where character string is not encapsulated inside quotation marks.
     *
     * @param StringIterator $string The string to parse
     * @param StringIterator $txt    The output string
     */
    private static function handleContiguousString(StringIterator $string, StringIterator $txt): void
    {
        while ($string->valid() && $string->isNot(static::WHITESPACE)) {
            $txt->append($string->current());
            $string->next();
        }
    }
}
