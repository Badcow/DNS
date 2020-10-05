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

use Badcow\DNS\Rdata\NS;
use PHPUnit\Framework\TestCase;

class NsTest extends TestCase
{
    public function testSetNsdname(): void
    {
        $target = 'foo.example.com.';
        $ns = new NS();
        $ns->setTarget($target);

        $this->assertEquals($target, $ns->getTarget());
    }

    public function testOutput(): void
    {
        $target = 'foo.example.com.';
        $ns = new NS();
        $ns->setTarget($target);

        $this->assertEquals($target, $ns->toText());
        $this->assertEquals($target, $ns->toText());
    }

    public function testFromText(): void
    {
        $text = 'host.example.com.';
        /** @var NS $cname */
        $cname = new NS();
        $cname->fromText($text);

        $this->assertEquals($text, $cname->getTarget());
    }

    public function testWire(): void
    {
        $host = 'host.example.com.';
        $expectation = chr(4).'host'.chr(7).'example'.chr(3).'com'.chr(0);

        /** @var NS $ns */
        $ns = new NS();
        $ns->fromWire($expectation);

        $this->assertEquals($expectation, $ns->toWire());
        $this->assertEquals($host, $ns->getTarget());
    }
}
