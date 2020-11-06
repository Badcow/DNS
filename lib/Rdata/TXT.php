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

    public function setText(?string $text): void
    {
        if (null === $text) {
            $this->text = null;

            return;
        }

        $this->text = stripslashes($text);
    }

    public function getText(): ?string
    {
        return (string) $this->text ?? '';
    }

    /**
     * {@inheritdoc}
     */
    public function toText(): string
    {
        return sprintf(
            Tokens::DOUBLE_QUOTES . '%s' . Tokens::DOUBLE_QUOTES, 
            addcslashes($this->text ?? '', Tokens::DOUBLE_QUOTES . '\\')
        );
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
    public function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): void
    {
        $rdLength = $rdLength ?? strlen($rdata);
        $this->setText(substr($rdata, $offset, $rdLength));
        $offset += $rdLength;
    }

    /**
     * {@inheritdoc}
     */
    public function fromText(string $text): void
    {
        $string = new StringIterator($text);
        $txt = new StringIterator();

        while ($string->valid()) {
            self::handleTxt($string, $txt);
            $string->next();
        }

        $this->setText((string) $txt);
    }

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
