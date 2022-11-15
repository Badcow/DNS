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

    public function dp_testToText(): array
    {
        return [
            //'what is tested' => [$text, $expectation]
            'quotes are escaped' => ['"This is some quoted text". It\'s a nice piece of text.', '"\"This is some quoted text\". It\'s a nice piece of text."'],
            'spaces are wraped in quotes' => [
                'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis vel lorem in massa elementum blandit nec sed massa. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec eu purus id arcu venenatis elementum in quis enim. Aenean at urna varius sapien dapibus.',
                '"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis vel lorem in massa elementum blandit nec sed massa. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec eu purus id arcu venenatis elementum in quis enim. Aenean at urna varius sapie" "n dapibus."',
            ],
        ];
    }

    /**
     * @dataProvider dp_testToText
     *
     * @param string $text        the input text value
     * @param string $expectation The expected output of TXT::toText()
     */
    public function testToText(string $text, string $expectation): void
    {
        $txt = new TXT();
        $txt->setText($text);

        $this->assertEquals($expectation, $txt->toText());
    }

    public function dp_testFromTxt(): array
    {
        return [
            //'what is tested' => [$text, $expectation]
            'chunked text literal' => ['"Some text;" " another some text"', 'Some text; another some text'],
            'string literal' => ['foobar', 'foobar'],
            'text with space without quotes' => ['foo bar', 'foo'],
            'trailing whitespace' => ["\t\t\tfoobar", 'foobar'],
            'integer literal' => ['3600', '3600'],
            'double escape sequence' => ['"double escape \\\\010"', 'double escape \010'],
        ];
    }

    /**
     * @dataProvider dp_testFromTxt
     */
    public function testFromTxt(string $text, string $expectation): void
    {
        $txt = new TXT();
        $txt->fromText($text);
        $this->assertEquals($expectation, $txt->getText());
    }

    public function testWire(): void
    {
        $expectation = 'This is some text. It\'s a nice piece of text.';
        $txt = new TXT();
        $txt->setText($expectation);

        $this->assertEquals($expectation, $txt->toWire());
        $fromWire = new TXT();
        $fromWire->fromWire($expectation);
        $this->assertEquals($txt, $fromWire);
    }
}
