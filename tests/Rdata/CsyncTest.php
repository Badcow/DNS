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

use Badcow\DNS\Rdata\A;
use Badcow\DNS\Rdata\AAAA;
use Badcow\DNS\Rdata\CSYNC;
use Badcow\DNS\Rdata\Factory;
use Badcow\DNS\Rdata\NS;
use PHPUnit\Framework\TestCase;

class CsyncTest extends TestCase
{
    public function testGetType(): void
    {
        $csync = new CSYNC();
        $this->assertEquals('CSYNC', $csync->getType());
    }

    public function testGetTypeCode(): void
    {
        $csync = new CSYNC();
        $this->assertEquals(62, $csync->getTypeCode());
    }

    public function testToText(): void
    {
        $csync = new CSYNC();
        $csync->setFlags(3);
        $csync->setSoaSerial(66);
        $csync->addType(A::TYPE);
        $csync->addType(NS::TYPE);
        $csync->addType(AAAA::TYPE);

        $this->assertEquals('66 3 A NS AAAA', $csync->toText());
    }

    public function testToWire(): void
    {
        $csync = new CSYNC();
        $csync->setFlags(3);
        $csync->setSoaSerial(66);
        $csync->addType(A::TYPE);
        $csync->addType(NS::TYPE);
        $csync->addType(AAAA::TYPE);

        $expectation = chr(0x00).chr(0x00).chr(0x00).chr(0x42).
            chr(0x00).chr(0x03).
            chr(0x00).chr(0x04).chr(0x60).chr(0x00).chr(0x00).chr(0x08);

        $this->assertEquals($expectation, $csync->toWire());
    }

    public function testFromText(): void
    {
        $csync = new CSYNC();
        $csync->setFlags(3);
        $csync->setSoaSerial(66);
        $csync->addType(A::TYPE);
        $csync->addType(NS::TYPE);
        $csync->addType(AAAA::TYPE);

        $fromText = new CSYNC();
        $fromText->fromText('66 3 A NS AAAA');
        $this->assertEquals($csync, $fromText);
    }

    /**
     * @throws \Badcow\DNS\Rdata\DecodeException
     * @throws \Badcow\DNS\Rdata\UnsupportedTypeException
     */
    public function testFromWire(): void
    {
        $wireFormat = chr(0x00).chr(0x00).chr(0x00).chr(0x42).
            chr(0x00).chr(0x03).
            chr(0x00).chr(0x04).chr(0x60).chr(0x00).chr(0x00).chr(0x08);

        $expectation = new CSYNC();
        $expectation->setFlags(3);
        $expectation->setSoaSerial(66);
        $expectation->addType(A::TYPE);
        $expectation->addType(NS::TYPE);
        $expectation->addType(AAAA::TYPE);

        $fromWire = new CSYNC();
        $fromWire->fromWire($wireFormat);

        $this->assertEquals($expectation, $fromWire);
    }

    public function testFactory(): void
    {
        $types = [A::TYPE, NS::TYPE, AAAA::TYPE];
        $csync = Factory::CSYNC(66, 3, $types);

        $this->assertEquals(66, $csync->getSoaSerial());
        $this->assertEquals(3, $csync->getFlags());
        $this->assertEquals($types, $csync->getTypes());
    }

    public function testClearTypes(): void
    {
        $csync = new CSYNC();
        $csync->addType('A');
        $this->assertCount(1, $csync->getTypes());
        $csync->clearTypes();
        $this->assertCount(0, $csync->getTypes());
    }
}
