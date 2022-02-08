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

use Badcow\DNS\Edns\Option\OptionInterface;
use Badcow\DNS\Edns\Option\TCP_KEEPALIVE;
use Badcow\DNS\Edns\Option\UnknownOption;
use Badcow\DNS\Rdata\DecodeException;
use Badcow\DNS\Rdata\OPT;
use PHPUnit\Framework\TestCase;

class OptTest extends TestCase
{
    public function testOutput(): void
    {
        $opt = new OPT();
        $this->assertEmpty($opt->toText());
    }

    public function testFromText(): void
    {
        $this->expectException(\Exception::class);
        $text = '';
        $opt = new OPT();
        $opt->fromText($text);
    }

    public function testFromWire1(): void
    {
        $wire = "\x00\x0B\x00\x00\x00\xFF\x00\x01A";

        $opt = new OPT();
        $opt->fromWire($wire);

        $options = $opt->getOptions();
        $this->assertCount(2, $options);
        $this->assertContainsOnlyInstancesOf(OptionInterface::class, $options);
        $this->assertInstanceOf(TCP_KEEPALIVE::class, $options[0]);
        $this->assertInstanceOf(UnknownOption::class, $options[1]);
        $this->assertEquals(255, $options[1]->getNameCode());
    }

    public function testFromWire2(): void
    {
        $this->expectException(DecodeException::class);
        $wire = "\x00\x00";
        $opt = new OPT();
        $opt->fromWire($wire);
    }

    public function testToWire(): void
    {
        $options = [];
        $options[0] = new TCP_KEEPALIVE();
        $options[1] = new UnknownOption();
        $options[1]->setOptionCode(255);
        $options[1]->setData('A');

        $opt = new OPT();
        $this->assertEmpty($opt->toWire());
        $opt->setOptions($options);

        $wire = "\x00\x0B\x00\x00\x00\xFF\x00\x01A";
        $this->assertEquals($wire, $opt->toWire());
    }
}
