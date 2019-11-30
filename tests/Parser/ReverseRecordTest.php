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
use Badcow\DNS\Rdata\PTR;
use PHPUnit\Framework\TestCase;

class ReverseRecordTest extends TestCase
{
    /**
     * @throws ParseException
     */
    public function testReverseRecord(): void
    {
        $ptr = '1    1080 IN    PTR  gw01.core.acme.com.';
        $zone = Parser::parse('50.100.200.in-addr.arpa.', $ptr);
        $rr = $zone->getResourceRecords()[0];

        $this->assertEquals('1', $rr->getName());
        $this->assertEquals(Classes::INTERNET, $rr->getClass());
        $this->assertEquals(PTR::TYPE, $rr->getType());
        $this->assertEquals('gw01.core.acme.com.', $rr->getRdata()->getTarget());
    }

    /**
     * @throws ParseException|\Exception
     */
    public function testParseReverseRecordFile(): void
    {
        $file = NormaliserTest::readFile(__DIR__.'/Resources/50.100.200.in-addr.arpa.db');
        $zone = Parser::parse('50.100.200.in-addr.arpa.', $file);

        $parentRecords = ParserTest::findRecord('@', $zone);
        $_1Records = ParserTest::findRecord('1', $zone);
        $_50Records = ParserTest::findRecord('50', $zone);
        $_150Records = ParserTest::findRecord('150', $zone);
        $_170Records = ParserTest::findRecord('170', $zone);

        $this->assertCount(11, $zone);
        $this->assertCount(3, $parentRecords);
        $this->assertCount(2, $_1Records);
        $this->assertCount(1, $_50Records);
        $this->assertCount(1, $_150Records);

        $_1 = $_1Records[0];
        $_50 = $_50Records[0];
        $_150 = $_150Records[0];
        $_170 = $_170Records[0];

        $this->assertEquals('1', $_1->getName());
        $this->assertEquals(1080, $_1->getTtl());
        $this->assertEquals(Classes::INTERNET, $_1->getClass());
        $this->assertEquals(PTR::TYPE, $_1->getType());
        $this->assertEquals('gw01.core.acme.com.', $_1->getRdata()->getTarget());

        $this->assertEquals('50', $_50->getName());
        $this->assertEquals(1080, $_50->getTtl());
        $this->assertEquals(Classes::INTERNET, $_50->getClass());
        $this->assertEquals(PTR::TYPE, $_50->getType());
        $this->assertEquals('mx1.acme.com.', $_50->getRdata()->getTarget());

        $this->assertEquals('150', $_150->getName());
        $this->assertEquals(200, $_150->getTtl());
        $this->assertEquals(Classes::INTERNET, $_150->getClass());
        $this->assertEquals(PTR::TYPE, $_150->getType());
        $this->assertEquals('smtp.example.com.', $_150->getRdata()->getTarget());

        $this->assertEquals('170', $_170->getName());
        $this->assertEquals(150, $_170->getTtl());
        $this->assertEquals(Classes::INTERNET, $_170->getClass());
        $this->assertEquals(PTR::TYPE, $_170->getType());
        $this->assertEquals('netscape.com.', $_170->getRdata()->getTarget());
    }
}
