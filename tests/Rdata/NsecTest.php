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
use Badcow\DNS\Rdata\Factory;
use Badcow\DNS\Rdata\MX;
use Badcow\DNS\Rdata\NS;
use Badcow\DNS\Rdata\NSEC;
use Badcow\DNS\Rdata\PTR;
use Badcow\DNS\Rdata\RRSIG;
use PHPUnit\Framework\TestCase;

class NsecTest extends TestCase
{
    public function testOutput(): void
    {
        $expectation = 'host.example.com. A MX RRSIG NSEC';

        $nsec = new NSEC();
        $nsec->setNextDomainName('host.example.com.');
        $nsec->addType(A::TYPE);
        $nsec->addType(MX::TYPE);
        $nsec->addType(RRSIG::TYPE);
        $nsec->addType(NSEC::TYPE);

        $this->assertEquals($expectation, $nsec->toText());
    }

    public function testFactory(): void
    {
        $nextDomain = 'host.example.com.';
        $bitMaps = [A::TYPE, MX::TYPE, RRSIG::TYPE, NSEC::TYPE];
        $nsec = Factory::NSEC($nextDomain, $bitMaps);

        $this->assertEquals($nextDomain, $nsec->getNextDomainName());
        $this->assertEquals($bitMaps, $nsec->getTypes());
    }

    public function testClearTypeMap(): void
    {
        $nsec = new NSEC();
        $nsec->addType(NS::TYPE);
        $nsec->addType(PTR::TYPE);

        $this->assertEquals([NS::TYPE, PTR::TYPE], $nsec->getTypes());
        $nsec->clearTypes();
        $this->assertEquals([], $nsec->getTypes());
    }

    public function testFromText(): void
    {
        $text = 'host.example.com. A MX RRSIG NSEC TYPE1234';
        /** @var NSEC $nsec */
        $nsec = new NSEC();
        $nsec->fromText($text);

        $this->assertEquals('host.example.com.', $nsec->getNextDomainName());
        $this->assertEquals(['A', 'MX', 'RRSIG', 'NSEC', 'TYPE1234'], $nsec->getTypes());
        $this->assertEquals($text, $nsec->toText());
    }

    public function testWire(): void
    {
        $hexMatrix = [
            0x04, ord('h'), ord('o'), ord('s'), ord('t'),
            0x07, ord('e'), ord('x'), ord('a'), ord('m'), ord('p'), ord('l'), ord('e'),
            0x03, ord('c'), ord('o'), ord('m'), 0x00,
            0x00, 0x06, 0x40, 0x01, 0x00, 0x00, 0x00, 0x03,
            0x01, 0x01, 0x40,
            0x80, 0x01, 0x40,
        ];

        $expectation = pack('C*', ...$hexMatrix);

        $text = 'host.example.com. A MX RRSIG NSEC CAA DLV';
        /** @var NSEC $nsec */
        $nsec = new NSEC();
        $nsec->fromText($text);

        $this->assertEquals($expectation, $nsec->toWire());
        $fromWire = new NSEC();
        $fromWire->fromWire($expectation);
        $this->assertEquals($nsec, $fromWire);
    }
}
