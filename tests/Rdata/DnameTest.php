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

use Badcow\DNS\Rdata\DNAME;
use PHPUnit\Framework\TestCase;

class DnameTest extends TestCase
{
    public function testSetTarget(): void
    {
        $target = 'foo.example.com.';
        $dname = new DNAME();
        $dname->setTarget($target);

        $this->assertEquals($target, $dname->getTarget());
    }

    public function testOutput(): void
    {
        $target = 'foo.example.com.';
        $dname = new DNAME();
        $dname->setTarget($target);

        $this->assertEquals($target, $dname->toText());
        $this->assertEquals($target, $dname->toText());
    }

    public function testFromText(): void
    {
        $text = 'host.example.com.';
        /** @var DNAME $cname */
        $cname = new DNAME();
        $cname->fromText($text);

        $this->assertEquals($text, $cname->getTarget());
    }

    public function testWire(): void
    {
        $host = 'host.example.com.';
        $expectation = chr(4).'host'.chr(7).'example'.chr(3).'com'.chr(0);

        /** @var DNAME $dname */
        $dname = new DNAME();
        $dname->fromWire($expectation);

        $this->assertEquals($expectation, $dname->toWire());
        $this->assertEquals($host, $dname->getTarget());
    }
}
