<?php
/*
 * This file is part of Badcow DNS Library.
 *
 * (c) Samuel Williams <sam@badcow.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Badcow\DNS\Test\Rdata;

use Badcow\DNS\Rdata\AaaaRdata;
use Badcow\DNS\Rdata\TxtRdata;

class TxtRdataTest extends \PHPUnit_Framework_TestCase
{
    public function testSetText()
    {
        $text = 'This is some text. It\'s a nice piece of text.';
        $txt = new TxtRdata;
        $txt->setText($text);

        $this->assertEquals($text, $txt->getText());
    }
}
