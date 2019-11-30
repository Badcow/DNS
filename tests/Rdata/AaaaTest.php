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

use Badcow\DNS\Rdata\AAAA;
use Badcow\DNS\Rdata\DecodeException;
use Badcow\DNS\Rdata\Factory;
use PHPUnit\Framework\TestCase;

class AaaaTest extends TestCase
{
    public function testToText(): void
    {
        $address = '2003:dead:beef:4dad:23:46:bb:101';
        $aaaa = new AAAA();
        $aaaa->setAddress($address);

        $this->assertEquals($address, $aaaa->toText());
    }

    public function testFromText(): void
    {
        $text = '2003:dead:beef:4dad:23:46:bb:101';
        /** @var AAAA $aaaa */
        $aaaa = AAAA::fromText($text);

        $this->assertEquals($text, $aaaa->getAddress());
    }

    public function testToWire(): void
    {
        $address = '2003:dead:beef:4dad:23:46:bb:101';
        $expectation = inet_pton($address);
        /** @var AAAA $aaaa */
        $aaaa = AAAA::fromWire($expectation);

        $this->assertEquals($expectation, $aaaa->toWire());
        $this->assertEquals($address, $aaaa->getAddress());
    }

    public function testGetType(): void
    {
        $aaaa = new AAAA();
        $this->assertEquals('AAAA', $aaaa->getType());
    }

    public function testGetTypeCode(): void
    {
        $aaaa = new AAAA();
        $this->assertEquals(28, $aaaa->getTypeCode());
    }

    /**
     * @throws DecodeException
     */
    public function testFromWire(): void
    {
        $wire = inet_pton('beef::1');
        $aaaa = AAAA::fromWire($wire);

        $this->assertEquals('beef::1', $aaaa->getAddress());
    }

    public function testFactory(): void
    {
        $aaaa = new AAAA();
        $aaaa->setAddress('2001:acad:1::');

        $this->assertEquals(Factory::AAAA('2001:acad:1::'), $aaaa);
    }

    /**
     * @throws DecodeException
     */
    public function testException(): void
    {
        $wire = pack('C18', 0x14, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x01, 0x00, 0x01); //2001:0:0:0:0:0:0:1:1
        $this->expectException(DecodeException::class);
        $this->expectExceptionMessage('Unable to decode AAAA record rdata from binary data "0x14 0x01 0x00 0x00 0x00 0x00 0x00 0x00 0x00 0x00 0x00 0x00 0x00 0x00 0x00 0x01 0x00 0x01"');
        AAAA::fromWire($wire);
    }
}
