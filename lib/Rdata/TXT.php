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

    const TYPE = 'TXT';
    const TYPE_CODE = 16;

    /**
     * @var string|null
     */
    private $text;

    /**
     * @param string|null $text
     */
    public function setText(?string $text): void
    {
        if (null === $text) {
            $this->text = null;

            return;
        }

        $this->text = stripslashes($text);
    }

    /**
     * @return string|null
     */
    public function getText(): ?string
    {
        return (string) $this->text ?? '';
    }

    /**
     * {@inheritdoc}
     */
    public function toText(): string
    {
        return sprintf('"%s"', addslashes($this->text ?? ''));
    }

    /**
     * {@inheritdoc}
     */
    public function toWire(): string
    {
        return $this->text ?? '';
    }

    /**
     * {@inheritdoc}
     */
    public static function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): RdataInterface
    {
        $rdLength = $rdLength ?? strlen($rdata);
        $txt = new static();
        $txt->setText(substr($rdata, $offset, $rdLength));
        $offset += $rdLength;

        return $txt;
    }

    /**
     * {@inheritdoc}
     */
    public static function fromText(string $text): RdataInterface
    {
        $string = new StringIterator($text);
        $txt = new StringIterator();

        while ($string->valid()) {
            self::handleTxt($string, $txt);
            $string->next();
        }

        $rdata = new static();
        $rdata->setText((string) $txt);

        return $rdata;
    }

    /**
     * @param StringIterator $string
     * @param StringIterator $txt
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
}
