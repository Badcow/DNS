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

use Badcow\DNS\Parser\ParseException;
use Badcow\DNS\Rdata\Factory;
use Badcow\DNS\Rdata\TLSA;
use PHPUnit\Framework\TestCase;

class TlsaTest extends TestCase
{
    private $cerAssociationData = '92003ba34942dc74152e2f2c408d29eca5a520e7f2e06bb944f4dca346baf63c1b177615d466f6c4b71c216a50292bd58c9ebdd2f74e38fe51ffd48c43326cbc';

    public function testGetType(): void
    {
        $tlsa = new TLSA();
        $this->assertEquals('TLSA', $tlsa->getType());
    }

    public function testGetTypeCode(): void
    {
        $tlsa = new TLSA();
        $this->assertEquals(52, $tlsa->getTypeCode());
    }

    public function testToText(): void
    {
        $tlsa = new TLSA();
        $tlsa->setCertificateUsage(0);
        $tlsa->setSelector(1);
        $tlsa->setMatchingType(2);
        $tlsa->setCertificateAssociationData(hex2bin($this->cerAssociationData));

        $expectation = '0 1 2 '.$this->cerAssociationData;
        $this->assertEquals($expectation, $tlsa->toText());
    }

    public function testToWire(): void
    {
        $tlsa = new TLSA();
        $tlsa->setCertificateUsage(0);
        $tlsa->setSelector(1);
        $tlsa->setMatchingType(2);
        $tlsa->setCertificateAssociationData(hex2bin($this->cerAssociationData));

        $expectation = chr(0).chr(1).chr(2).hex2bin($this->cerAssociationData);

        $this->assertEquals($expectation, $tlsa->toWire());
    }

    public function testFromWire(): void
    {
        $wireFormat = chr(0).chr(1).chr(2).hex2bin($this->cerAssociationData);
        $tlsa = new TLSA();
        $tlsa->fromWire($wireFormat);

        $this->assertEquals(0, $tlsa->getCertificateUsage());
        $this->assertEquals(1, $tlsa->getSelector());
        $this->assertEquals(2, $tlsa->getMatchingType());
        $this->assertEquals(hex2bin($this->cerAssociationData), $tlsa->getCertificateAssociationData());

        $rdLength = strlen($wireFormat);
        $wireFormat = 'abc'.$wireFormat;
        $offset = 3;

        $fromWire = new TLSA();
        $fromWire->fromWire($wireFormat, $offset, $rdLength);
        $this->assertEquals($tlsa, $fromWire);
        $this->assertEquals(3 + $rdLength, $offset);
    }

    /**
     * @throws ParseException
     */
    public function testFromText(): void
    {
        $text = '0 1 2 '.$this->cerAssociationData;
        $tlsa = new TLSA();
        $tlsa->fromText($text);

        $this->assertEquals(0, $tlsa->getCertificateUsage());
        $this->assertEquals(1, $tlsa->getSelector());
        $this->assertEquals(2, $tlsa->getMatchingType());
        $this->assertEquals(hex2bin($this->cerAssociationData), $tlsa->getCertificateAssociationData());
    }

    public function testFactory(): void
    {
        $tlsa = Factory::TLSA(0, 1, 2, hex2bin($this->cerAssociationData));

        $this->assertEquals(0, $tlsa->getCertificateUsage());
        $this->assertEquals(1, $tlsa->getSelector());
        $this->assertEquals(2, $tlsa->getMatchingType());
        $this->assertEquals(hex2bin($this->cerAssociationData), $tlsa->getCertificateAssociationData());
    }

    public function testExceptions(): void
    {
        $tlsa = new TLSA();
        $this->expectException(\InvalidArgumentException::class);
        $tlsa->setCertificateUsage(9);

        $this->expectException(\InvalidArgumentException::class);
        $tlsa->setSelector(12);

        $this->expectException(\InvalidArgumentException::class);
        $tlsa->setMatchingType(-1);
    }

    /**
     * @throws ParseException
     */
    public function testMalformedHexValueThrowsException(): void
    {
        $text = '0 1 2 92003ba34942dc74152e2f2c408d29eca5a520g';
        $this->expectException(ParseException::class);
        $tlsa = new TLSA();
        $tlsa->fromText($text);
    }
}
