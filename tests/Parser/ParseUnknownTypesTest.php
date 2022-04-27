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

namespace Badcow\DNS\Tests\Parser;

use Badcow\DNS\Classes;
use Badcow\DNS\Parser\ParseException;
use Badcow\DNS\Parser\Parser;
use Badcow\DNS\Rdata\A;
use Badcow\DNS\Rdata\PolymorphicRdata;
use Badcow\DNS\Rdata\UnknownType;
use Badcow\DNS\ResourceRecord;
use PHPUnit\Framework\TestCase;

class ParseUnknownTypesTest extends TestCase
{
    /**
     * @throws ParseException
     */
    public function testUnknownType(): void
    {
        $text = 'dickens.example.com. CLASS45 1800 TYPE1859 \# 20 412054616c65206f662054776f20436974696573';
        $binData = hex2bin('412054616c65206f662054776f20436974696573');
        $zone = Parser::parse('example.com.', $text);
        $this->assertCount(1, $zone);
        /** @var ResourceRecord $rr */
        $rr = $zone[0];

        $this->assertEquals('dickens.example.com.', $rr->getName());
        $this->assertEquals('CLASS45', $rr->getClass());
        $this->assertEquals(45, $rr->getClassId());
        $this->assertEquals('TYPE1859', $rr->getType());
        $this->assertEquals(1800, $rr->getTtl());
        $this->assertInstanceOf(UnknownType::class, $rr->getRdata());
        $this->assertEquals($binData, $rr->getRdata()->getData());
        $this->assertEquals(1859, $rr->getRdata()->getTypeCode());
    }

    /**
     * @throws ParseException
     */
    public function testPolymorphicType(): void
    {
        $text = 'dickens.example.com. IN 1800 RESERVED "A Tale of Two Cities"';
        $zone = Parser::parse('example.com.', $text);
        $this->assertCount(1, $zone);
        /** @var ResourceRecord $rr */
        $rr = $zone[0];

        $this->assertEquals('dickens.example.com.', $rr->getName());
        $this->assertEquals('IN', $rr->getClass());
        $this->assertEquals(1, $rr->getClassId());
        $this->assertEquals('RESERVED', $rr->getType());
        $this->assertEquals(0xFFFF, $rr->getRdata()->getTypeCode());
        $this->assertEquals(1800, $rr->getTtl());
        $this->assertInstanceOf(PolymorphicRdata::class, $rr->getRdata());
        $this->assertEquals('"A Tale of Two Cities"', $rr->getRdata()->getData());
    }

    public function testSupportedTypeInUnknownFormatIsOutputtedAsCorrectType(): void
    {
        //  files.example.com. IN 3600 IN A 192.168.1.100
        $record = 'files.example.com.  IN  3600    TYPE1   \# 4 c0 a8 01 64';
        $zone = Parser::parse('example.com.', $record);
        $this->assertCount(1, $zone);
        /** @var ResourceRecord $rr */
        $rr = $zone[0];

        $this->assertEquals('files.example.com.', $rr->getName());
        $this->assertEquals(Classes::INTERNET, $rr->getClass());
        $this->assertEquals(3600, $rr->getTtl());
        $this->assertInstanceOf(A::class, $rr->getRdata());
        $this->assertEquals('192.168.1.100', $rr->getRdata()->getAddress());
    }
}
