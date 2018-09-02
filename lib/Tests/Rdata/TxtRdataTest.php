<?php

/*
 * This file is part of Badcow DNS Library.
 *
 * (c) Samuel Williams <sam@badcow.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Badcow\DNS\Tests\Rdata;

use Badcow\DNS\Rdata\TXT;


class TxtRdataTest extends \PHPUnit\Framework\TestCase
{
    public function testSetText()
    {
        $text = 'This is some text. It\'s a nice piece of text.';
        $txt = new TXT();
        $txt->setText($text);

        $this->assertEquals($text, $txt->getText());
    }

    public function testOutput()
    {
        $text = 'This is some text. It\'s a nice piece of text.';
        $expected = '"This is some text. It\\\'s a nice piece of text."';
        $txt = new TXT();
        $txt->setText($text);

        $this->assertEquals($expected, $txt->output());
    }
}
