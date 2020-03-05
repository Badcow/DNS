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

    const TYPE = 'HINFO';
    const TYPE_CODE = 13;

    /**
     * @var string|null
     */
    private $cpu;

    /**
     * @var string|null
     */
    private $os;

    /**
     * @param string $cpu
     */
    public function setCpu(string $cpu): void
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

    /**
     * @param string $os
     */
    public function setOs(string $os): void
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

    /**
     * {@inheritdoc}
     */
    public function toText(): string
    {
        return sprintf('"%s" "%s"', $this->cpu ?? '', $this->os ?? '');
    }

    /**
     * {@inheritdoc}
     */
    public function toWire(): string
    {
        return $this->toText();
    }

    /**
     * {@inheritdoc}
     *
     * @return HINFO
     */
    public static function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): RdataInterface
    {
        $offset += strlen($rdata);

        return self::fromText($rdata);
    }

    /**
     * {@inheritdoc}
     *
     * @return HINFO
     */
    public static function fromText(string $text): RdataInterface
    {
        $string = new StringIterator($text);
        $hinfo = new self();
        $hinfo->setCpu(self::extractText($string));
        $hinfo->setOs(self::extractText($string));

        return $hinfo;
    }

    /**
     * @param StringIterator $string
     *
     * @return string
     */
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
