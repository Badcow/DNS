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
 * @see https://tools.ietf.org/html/rfc1035#section-3.3.2
 */
class HINFO implements RdataInterface
{
    use RdataTrait;

    public const TYPE = 'HINFO';
    public const TYPE_CODE = 13;

    /**
     * @var string|null
     */
    private $cpu;

    /**
     * @var string|null
     */
    private $os;

    public function setCpu(?string $cpu): void
    {
        $this->cpu = $cpu;
    }

    /**
     * @return string
     */
    public function getCpu(): ?string
    {
        return $this->cpu;
    }

    public function setOs(?string $os): void
    {
        $this->os = $os;
    }

    /**
     * @return string
     */
    public function getOs(): ?string
    {
        return $this->os;
    }

    public function toText(): string
    {
        return sprintf('"%s" "%s"', $this->cpu ?? '', $this->os ?? '');
    }

    public function toWire(): string
    {
        return $this->toText();
    }

    public function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): void
    {
        $this->fromText(substr($rdata, $offset, $rdLength ?? strlen($rdata)));
        $offset += $rdLength;
    }

    public function fromText(string $text): void
    {
        $string = new StringIterator($text);
        $this->setCpu(self::extractText($string));
        $this->setOs(self::extractText($string));
    }

    private static function extractText(StringIterator $string): string
    {
        $txt = new StringIterator();

        if ($string->is(Tokens::DOUBLE_QUOTES)) {
            TXT::handleTxt($string, $txt);
            $string->next();
        } else {
            while ($string->isNot(Tokens::SPACE) && $string->valid()) {
                $txt->append($string->current());
                $string->next();
            }
        }

        if ($string->is(Tokens::SPACE)) {
            $string->next();
        }

        return (string) $txt;
    }
}
