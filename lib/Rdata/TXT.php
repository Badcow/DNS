<?php

/*
 * This file is part of Badcow DNS Library.
 *
 * (c) Samuel Williams <sam@badcow.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Badcow\DNS\Rdata;

/**
 * @see https://tools.ietf.org/html/rfc1035#section-3.3.14
 */
class TXT implements RdataInterface
{
    use RdataTrait;

    const TYPE = 'TXT';

    /**
     * @var string
     */
    private $text;

    /**
     * @param $text
     */
    public function setText(string $text): void
    {
        $this->text = addslashes($text);
    }

    public function getText(): ?string
    {
        return stripslashes($this->text);
    }

    /**
     * {@inheritdoc}
     */
    public function output(): string
    {
        return '"'.$this->text.'"';
    }
}
