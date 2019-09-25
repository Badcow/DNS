<?php

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
use Badcow\DNS\Parser\Parser;
use Badcow\DNS\Rdata\PTR;
use PHPUnit\Framework\TestCase;

class ReverseRecordTest extends TestCase
{
    /**
     * @throws \Badcow\DNS\Parser\ParseException
     */
    public function testReverseRecord()
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
     * @throws \Badcow\DNS\Parser\ParseException|\Exception
     */
    public function testParseReverseRecordFile()
    {
        $file = NormaliserTest::readFile(__DIR__.'/Resources/50.100.200.in-addr.arpa.db');
        $zone = Parser::parse('50.100.200.in-addr.arpa.', $file);

        $parentRecords = ParserTest::findRecord('@', $zone);
        $_1Records = ParserTest::findRecord('1', $zone);
        $_50Records = ParserTest::findRecord('50', $zone);

        $this->assertCount(3, $parentRecords);

        $this->assertCount(1, $_50Records);
        $this->assertCount(2, $_1Records);

        $_1 = $_1Records[0];
        $_50 = $_50Records[0];

        $this->assertEquals(1080, $_50->getTtl());
        $this->assertEquals(PTR::TYPE, $_50->getType());
        $this->assertEquals(Classes::INTERNET, $_50->getClass());
        $this->assertEquals('mx1.acme.com.', $_50->getRdata()->getTarget());
    }
}
