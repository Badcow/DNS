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
use Badcow\DNS\Parser\ParseException;
use Badcow\DNS\Parser\Parser;
use Badcow\DNS\Rdata\APL;
use Badcow\DNS\Rdata\UnsupportedTypeException;
use Badcow\DNS\Zone;
use Badcow\DNS\Rdata\Factory;
use Badcow\DNS\ResourceRecord;
use Badcow\DNS\AlignedBuilder;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{
    /**
     * Build a test zone.
     *
     * @return Zone
     */
    private function getTestZone(): Zone
    {
        $zone = new Zone('example.com.');
        $zone->setDefaultTtl(3600);

        $soa = new ResourceRecord();
        $soa->setName('@');
        $soa->setRdata(Factory::Soa(
            'example.com.',
            'post.example.com.',
            '2014110501',
            3600,
            14400,
            604800,
            3600
        ));

        $ns1 = new ResourceRecord();
        $ns1->setName('@');
        $ns1->setRdata(Factory::Ns('ns1.nameserver.com.'));

        $ns2 = new ResourceRecord();
        $ns2->setName('@');
        $ns2->setRdata(Factory::Ns('ns2.nameserver.com.'));

        $a = new ResourceRecord();
        $a->setName('sub.domain');
        $a->setRdata(Factory::A('192.168.1.42'));
        $a->setComment('This is a local ip.');

        $a6 = new ResourceRecord();
        $a6->setName('ipv6.domain');
        $a6->setRdata(Factory::Aaaa('::1'));
        $a6->setComment('This is an IPv6 domain.');

        $mx1 = new ResourceRecord();
        $mx1->setName('@');
        $mx1->setRdata(Factory::Mx(10, 'mail-gw1.example.net.'));

        $mx2 = new ResourceRecord();
        $mx2->setName('@');
        $mx2->setRdata(Factory::Mx(20, 'mail-gw2.example.net.'));

        $mx3 = new ResourceRecord();
        $mx3->setName('@');
        $mx3->setRdata(Factory::Mx(30, 'mail-gw3.example.net.'));

        $dname = new ResourceRecord('hq', Factory::Dname('syd.example.com.'));

        $loc = new ResourceRecord();
        $loc->setName('canberra');
        $loc->setRdata(Factory::Loc(
            -35.3075,   //Lat
            149.1244,   //Lon
            500,        //Alt
            20.12,      //Size
            200.3,      //HP
            300.1       //VP
        ));
        $loc->setComment('This is Canberra');

        $zone->addResourceRecord($soa);
        $zone->addResourceRecord($ns1);
        $zone->addResourceRecord($ns2);
        $zone->addResourceRecord($a);
        $zone->addResourceRecord($a6);
        $zone->addResourceRecord($dname);
        $zone->addResourceRecord($mx1);
        $zone->addResourceRecord($mx2);
        $zone->addResourceRecord($mx3);
        $zone->addResourceRecord($loc);

        return $zone;
    }

    /**
     * Parser creates valid dns object.
     *
     * @throws \Badcow\DNS\Parser\ParseException
     * @throws UnsupportedTypeException
     */
    public function testParserCreatesValidDnsObject()
    {
        $zoneBuilder = new AlignedBuilder();
        $zone = $zoneBuilder->build($this->getTestZone());

        $expectation = $this->getTestZone();
        foreach ($expectation->getResourceRecords() as $rr) {
            $rr->setComment('');
        }

        $this->assertEquals($expectation, Parser::parse('example.com.', $zone));
    }

    /**
     * Parser ignores control entries other than TTL.
     *
     * @throws ParseException
     * @throws UnsupportedTypeException
     */
    public function testParserIgnoresControlEntriesOtherThanTtl()
    {
        $file = file_get_contents(__DIR__.'/Resources/testCollapseMultilines_sample.txt');
        $zone = Parser::parse('example.com.', $file);

        $this->assertEquals('example.com.', $zone->getName());
        $this->assertEquals('::1', $this->findRecord('ipv6.domain', $zone)[0]->getRdata()->getAddress());
        $this->assertEquals(1337, $zone->getDefaultTtl());
    }

    /**
     * Parser can handle convoluted zone record.
     *
     * @throws \Badcow\DNS\Parser\ParseException
     * @throws \Badcow\DNS\Rdata\UnsupportedTypeException
     */
    public function testParserCanHandleConvolutedZoneRecord()
    {
        $file = file_get_contents(__DIR__.'/Resources/testConvolutedZone_sample.txt');
        $zone = Parser::parse('example.com.', $file);
        $this->assertEquals(3600, $zone->getDefaultTtl());
        $this->assertCount(28, $zone->getResourceRecords());

        $txt = new ResourceRecord(
            'testtxt',
            Factory::txt('v=DKIM1; k=rsa; p=MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBg'.
                'QDZKI3U+9acu3NfEy0NJHIPydxnPLPpnAJ7k2JdrsLqAK1uouMudHI20pgE8RMldB/TeW'.
                'KXYoRidcGCZWXleUzldDTwZAMDQNpdH1uuxym0VhoZpPbI1RXwpgHRTbCk49VqlC'),
            600,
            Classes::INTERNET
        );

        $txt2 = 'Some text another Some text';

        $this->assertEquals($txt, $this->findRecord($txt->getName(), $zone)[0]);
        $this->assertEquals($txt2, $this->findRecord('test', $zone)[0]->getRdata()->getText());
        $this->assertCount(1, $this->findRecord('xn----7sbfndkfpirgcajeli2a4pnc.xn----7sbbfcqfo2cfcagacemif0ap5q', $zone));
        $this->assertCount(4, $this->findRecord('testmx', $zone));
    }

    /**
     * @throws ParseException
     * @throws UnsupportedTypeException
     */
    public function testCanHandlePolymorphicRdata()
    {
        $string = 'example.com. 7200 IN A6 2001:acad::1337; This is invalid.';
        $zone = Parser::parse('example.com.', $string);
        $rr = $zone->getResourceRecords()[0];

        $this->assertEquals('A6', $rr->getRdata()->getType());
        $this->assertEquals('2001:acad::1337', $rr->getRdata()->output());
    }

    /**
     * @throws ParseException
     * @throws UnsupportedTypeException
     */
    public function testParserCanHandleAplRecords()
    {
        $file = file_get_contents(__DIR__.'/Resources/testCollapseMultilines_sample.txt');
        $zone = Parser::parse('example.com.', $file);

        /** @var APL $apl */
        $apl = $this->findRecord('multicast', $zone)[0]->getRdata();
        $this->assertCount(2, $apl->getIncludedAddressRanges());
        $this->assertCount(2, $apl->getExcludedAddressRanges());

        $this->assertEquals('192.168.0.0/23', (string) $apl->getIncludedAddressRanges()[0]);
        $this->assertEquals('2001:acad:1::8/128', (string) $apl->getExcludedAddressRanges()[1]);
    }

    /**
     * @expectedException \Badcow\DNS\Parser\ParseException
     * @expectedExceptionMessage "3:192.168.0.64/30" is not a valid IP range.
     *
     * @throws ParseException
     * @throws UnsupportedTypeException
     */
    public function testMalformedAplRecordThrowsException1()
    {
        $zone = 'multicast 3600 IN APL 3:192.168.0.64/30';

        Parser::parse('example.com.', $zone);
    }

    /**
     * @expectedException \Badcow\DNS\Parser\ParseException
     * @expectedExceptionMessage "!1-192.168.0.64/30" is not a valid IP range.
     *
     * @throws ParseException
     * @throws UnsupportedTypeException
     */
    public function testMalformedAplRecordThrowsException2()
    {
        $zone = 'multicast 3600 IN APL !1-192.168.0.64/30';

        Parser::parse('example.com.', $zone);
    }

    /**
     * Find all records in a Zone named $name.
     *
     * @param string $name
     * @param Zone   $zone
     *
     * @return array
     */
    private function findRecord(string $name, Zone $zone): array
    {
        $records = [];

        foreach ($zone->getResourceRecords() as $resourceRecord) {
            if ($name === $resourceRecord->getName()) {
                $records[] = $resourceRecord;
            }
        }

        return $records;
    }
}
