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

namespace Badcow\DNS\Tests\Rdata;

use Badcow\DNS\Rdata\TXT;
use PHPUnit\Framework\TestCase;

class TxtTest extends TestCase
{
    public function testSetText(): void
    {
        $text = 'This is some text. It\'s a nice piece of text.';
        $txt = new TXT();
        $txt->setText($text);

        $this->assertEquals($text, $txt->getText());
    }

    public function testOutput(): void
    {
        $text = 'This is some text. It\'s a nice piece of text.';
        $expected = '"This is some text. It\\\'s a nice piece of text."';
        $txt = new TXT();
        $txt->setText($text);

        $this->assertEquals($expected, $txt->toText());
        $this->assertEquals($expected, $txt->toText());
    }

    public function testFromTxt(): void
    {
        $text = '"Some text;" " another some text"';
        $expectation = 'Some text; another some text';

        /** @var TXT $txt */
        $txt = TXT::fromText($text);
        $this->assertEquals($expectation, $txt->getText());
    }

    public function testWire(): void
    {
        $expectation = 'This is some text. It\'s a nice piece of text.';
        $txt = new TXT();
        $txt->setText($expectation);

        $this->assertEquals($expectation, $txt->toWire());
        $this->assertEquals($txt, TXT::fromWire($expectation));
    }
}
