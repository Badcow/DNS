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

    public function testSetAddress(): void
    {
        $address = 'fe80::1';
        $aaaa = new AAAA();
        $aaaa->setAddress($address);

        $this->assertEquals($address, $aaaa->getAddress());

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The address "abc" is not a valid IPv6 address.');
        $aaaa->setAddress('abc');
    }

    public function testFromText(): void
    {
        $text = '2003:dead:beef:4dad:23:46:bb:101';
        /** @var AAAA $aaaa */
        $aaaa = new AAAA();
        $aaaa->fromText($text);

        $this->assertEquals($text, $aaaa->getAddress());
    }

    /**
     * @throws DecodeException
     */
    public function testToWire(): void
    {
        $address = '2003:dead:beef:4dad:23:46:bb:101';
        $expectation = inet_pton($address);
        /** @var AAAA $aaaa */
        $aaaa = new AAAA();
        $aaaa->fromWire($expectation);

        $this->assertEquals($expectation, $aaaa->toWire());
        $this->assertEquals($address, $aaaa->getAddress());
    }

    public function testToWireThrowsExceptionIfAddressIsMalformed(): void
    {
        $aaaa_prime = new class() extends AAAA {
            public function __construct()
            {
                $this->address = 'abc';
            }
        };

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The IP address "abc" cannot be encoded. Check that it is a valid IP address.');
        $aaaa_prime->toWire();
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
        $wire = chr(0x07).inet_pton('beef::1').chr(0x07);
        $offset = 1;
        $aaaa = new AAAA();
        $aaaa->fromWire($wire, $offset);

        $this->assertEquals('beef::1', $aaaa->getAddress());
        $this->assertEquals(17, $offset);

        $wire = pack('C3', 0x61, 0x62, 0x63);
        $aaaa = new AAAA();
        $this->expectException(DecodeException::class);
        $aaaa->fromWire($wire);
    }

    public function testFactory(): void
    {
        $aaaa = new AAAA();
        $aaaa->setAddress('2001:acad:1::');

        $this->assertEquals(Factory::AAAA('2001:acad:1::'), $aaaa);
    }
}
