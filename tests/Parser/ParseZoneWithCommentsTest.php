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

use Badcow\DNS\Parser\Parser;
use Badcow\DNS\Rdata\A;
use Badcow\DNS\Rdata\AAAA;
use Badcow\DNS\Rdata\NS;
use Badcow\DNS\Rdata\SOA;
use Badcow\DNS\Rdata\TXT;
use PHPUnit\Framework\TestCase;

class ParseZoneWithCommentsTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testParseZoneWithComments()
    {
        $zoneFile = NormaliserTest::readFile(__DIR__.'/Resources/testCollapseMultilines_sample.txt');
        $zone = Parser::parse('example.com.', $zoneFile, true);

        $nsRecords = ParserTest::findRecord('@', $zone, NS::TYPE);
        $this->assertCount(2, $nsRecords);

        $mailTxtRecords = ParserTest::findRecord('mail', $zone, TXT::TYPE);
        $this->assertCount(1, $mailTxtRecords);
        $this->assertNull($mailTxtRecords[0]->getComment());

        $sub_domainRecords = ParserTest::findRecord('sub.domain', $zone, A::TYPE);
        $this->assertCount(1, $sub_domainRecords);
        $this->assertEquals('This is a local ip.', $sub_domainRecords[0]->getComment());

        $ipv6_domainRecords = ParserTest::findRecord('ipv6.domain', $zone, AAAA::TYPE);
        $this->assertCount(1, $ipv6_domainRecords);
        $this->assertEquals('This is an IPv6 domain.', $ipv6_domainRecords[0]->getComment());

        $soaRecords = ParserTest::findRecord('@', $zone, SOA::TYPE);
        $this->assertCount(1, $soaRecords);
        $this->assertEquals('MNAME RNAME SERIAL REFRESH RETRY EXPIRE MINIMUM This is my Start of Authority Record; AKA SOA.', $soaRecords[0]->getComment());
    }

    /**
     * @throws \Exception
     */
    public function testCommentOnlyLinesParse()
    {
        $zoneFile = NormaliserTest::readFile(__DIR__.'/Resources/testCollapseMultilines_sample.txt');
        $zone = Parser::parse('example.com.', $zoneFile, true);

        $nullEntries = ParserTest::findRecord(null, $zone, null);
        $this->assertCount(4, $nullEntries);
        $this->assertEquals('NS RECORDS', $nullEntries[0]->getComment());
        $this->assertEquals('A RECORDS', $nullEntries[1]->getComment());
        $this->assertEquals('AAAA RECORDS', $nullEntries[2]->getComment());
        $this->assertEquals('MX RECORDS', $nullEntries[3]->getComment());
    }
}
