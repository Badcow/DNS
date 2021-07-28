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

use Badcow\DNS\Rdata\ALIAS;
use PHPUnit\Framework\TestCase;

class AliasTest extends TestCase
{
    public function testOutput(): void
    {
        $target = 'foo.example.com.';
        $alias = new ALIAS();
        $alias->setTarget($target);

        $this->assertEquals($target, $alias->toText());
    }

    public function testFromText(): void
    {
        $text = 'host.example.com.';
        /** @var ALIAS $alias */
        $alias = new ALIAS();
        $alias->fromText($text);

        $this->assertEquals($text, $alias->getTarget());
    }

    public function testWire(): void
    {
        $host = 'host.example.com.';
        $expectation = chr(4).'host'.chr(7).'example'.chr(3).'com'.chr(0);

        /** @var ALIAS $alias */
        $alias = new ALIAS();
        $alias->fromWire($expectation);

        $this->assertEquals($expectation, $alias->toWire());
        $this->assertEquals($host, $alias->getTarget());

        //Test that toWire() will throw an exception if no target is set.
        $alias = new ALIAS();
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Target must be set.');
        $alias->toWire();
    }
}
