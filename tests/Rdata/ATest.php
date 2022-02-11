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
use Badcow\DNS\Rdata\DecodeException;
use PHPUnit\Framework\TestCase;

class ATest extends TestCase
{
    /**
     * @var A
     */
    private $aRdata;

    public function setUp(): void
    {
        $this->aRdata = new A();
    }

    public function testGetType(): void
    {
        $this->assertEquals('A', $this->aRdata->getType());
    }

    public function testSetAddress(): void
    {
        $address = '192.168.1.1';
        $this->aRdata->setAddress($address);

        $this->assertEquals($address, $this->aRdata->getAddress());

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The address "abc" is not a valid IPv4 address.');
        $this->aRdata->setAddress('abc');
    }

    public function testOutput(): void
    {
        $address = '192.168.1.1';
        $this->aRdata->setAddress($address);

        $this->assertEquals($address, $this->aRdata->toText());
        $this->assertEquals($address, $this->aRdata->toText());
    }

    public function testFromText(): void
    {
        $text = '200.100.50.1';
        /** @var A $a */
        $a = new A();
        $a->fromText($text);

        $this->assertEquals($text, $a->getAddress());
    }

    /**
     * @throws DecodeException
     */
    public function testWire(): void
    {
        $address = '200.100.50.1';
        $expectation = inet_pton($address);
        /** @var A $a */
        $a = new A();
        $a->fromWire($expectation);

        $this->assertEquals($expectation, $a->toWire());
        $this->assertEquals($address, $a->getAddress());
    }

    public function testToWireThrowsExceptionIfAddressIsMalformed(): void
    {
        $a_prime = new class() extends A {
            public function __construct()
            {
                $this->address = 'abc';
            }
        };

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The IP address "abc" cannot be encoded. Check that it is a valid IP address.');
        $a_prime->toWire();
    }

    /**
     * @throws DecodeException
     */
    public function testFromWire(): void
    {
        $wire = pack('C6', 0x07, 0xC0, 0xFF, 0x01, 0x01, 0x07); //⍾192.255.1.1⍾
        $offset = 1;
        /** @var A $a */
        $a = new A();
        $a->fromWire($wire, $offset);

        $this->assertEquals('192.255.1.1', $a->getAddress());
        $this->assertEquals(chr(0x07), $wire[$offset]);

        $wire = pack('C3', 0x61, 0x62, 0x63);
        $a = new A();
        $this->expectException(DecodeException::class);
        $a->fromWire($wire);
    }
}
