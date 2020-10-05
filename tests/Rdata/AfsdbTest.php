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

use Badcow\DNS\Rdata\AFSDB;
use Badcow\DNS\Rdata\Factory;
use PHPUnit\Framework\TestCase;

/**
 * {@link https://tools.ietf.org/html/rfc1183}.
 */
class AfsdbTest extends TestCase
{
    public function testOutput(): void
    {
        $hostname = 'foo.example.com.';
        $afsdb = new AFSDB();
        $afsdb->setHostname($hostname);
        $afsdb->setSubType(2);

        $this->assertEquals('2 foo.example.com.', $afsdb->toText());
    }

    public function testOutputThrowsExceptionWhenMissingSubType(): void
    {
        $afsdb = new AFSDB();
        $afsdb->setHostname('foo.example.com.');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No sub-type has been set on AFSDB object.');
        $afsdb->toText();
    }

    public function testOutputThrowsExceptionWhenMissingHostname(): void
    {
        $afsdb = new AFSDB();
        $afsdb->setSubType(15);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No hostname has been set on AFSDB object.');
        $afsdb->toText();
    }

    public function testFromText(): void
    {
        $text = '2 foo.example.com.';
        /** @var AFSDB $afsdb */
        $afsdb = new AFSDB();
        $afsdb->fromText($text);

        $this->assertEquals(2, $afsdb->getSubType());
        $this->assertEquals('foo.example.com.', $afsdb->getHostname());
    }

    public function testWire(): void
    {
        $afsdb = new AFSDB();
        $afsdb->setHostname('foo.example.com.');
        $afsdb->setSubType(2);

        $expectation = pack('n', 2).chr(3).'foo'.chr(7).'example'.chr(3).'com'.chr(0);

        $fromWire = new AFSDB();
        $fromWire->fromWire($expectation);

        $this->assertEquals($expectation, $afsdb->toWire());
        $this->assertEquals($afsdb, $fromWire);
    }

    public function testFactory(): void
    {
        $afsdb = Factory::AFSDB(2, 'foo.example.com.');

        $this->assertInstanceOf(AFSDB::class, $afsdb);
        $this->assertEquals('2 foo.example.com.', $afsdb->toText());
    }
}
