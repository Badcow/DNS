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
    public function setText($text)
    {
        $this->text = addslashes($text);
    }

    public function getText()
    {
        return stripslashes($this->text);
    }

    /**
     * {@inheritdoc}
     */
    public function output()
    {
        return '"'.$this->text.'"';
    }
}
