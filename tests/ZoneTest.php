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

namespace Badcow\DNS\Tests;

use Badcow\DNS\AlignedBuilder;
use Badcow\DNS\Classes;
use Badcow\DNS\Rdata\APL;
use Badcow\DNS\Rdata\Factory;
use Badcow\DNS\ResourceRecord;
use Badcow\DNS\Zone;
use Badcow\DNS\ZoneBuilder;
use PhpIP\IPBlock;
use PHPUnit\Framework\TestCase;

class ZoneTest extends TestCase
{
    private static function buildTestZone(): Zone
    {
        $zone = new Zone('example.com.');
        $zone->setDefaultTtl(3600);

        $soa = new ResourceRecord();
        $soa->setName('@');
        $soa->setRdata(Factory::SOA(
            '@',
            'post',
            2014110501,
            3600,
            14400,
            604800,
            3600
        ));

        $soa->setClass(Classes::INTERNET);

        $ns1 = new ResourceRecord();
        $ns1->setName('@');
        $ns1->setRdata(Factory::NS('ns1.nameserver.com.'));

        $ns2 = new ResourceRecord();
        $ns2->setName('@');
        $ns2->setRdata(Factory::NS('ns2.nameserver.com.'));

        $a = new ResourceRecord();
        $a->setName('sub.domain');
        $a->setRdata(Factory::A('192.168.1.42'));
        $a->setComment('This is a local ip.');

        $a6 = new ResourceRecord();
        $a6->setName('ipv6.domain');
        $a6->setRdata(Factory::AAAA('::1'));
        $a6->setComment('This is an IPv6 domain.');

        $mx1 = new ResourceRecord();
        $mx1->setName('@');
        $mx1->setRdata(Factory::MX(10, 'mail-gw1.example.net.'));

        $mx2 = new ResourceRecord();
        $mx2->setName('@');
        $mx2->setRdata(Factory::MX(20, 'mail-gw2.example.net.'));

        $mx3 = new ResourceRecord();
        $mx3->setName('@');
        $mx3->setRdata(Factory::MX(30, 'mail-gw3.example.net.'));

        $loc = new ResourceRecord();
        $loc->setName('canberra');
        $loc->setRdata(Factory::LOC(
            -35.3075,   //Lat
            149.1244,   //Lon
            500,        //Alt
            20.12,      //Size
            200.3,      //HP
            300.1       //VP
        ));
        $loc->setComment('This is Canberra');

        $srv = new ResourceRecord();
        $srv->setName('_ftp._tcp');
        $srv->setClass('IN');
        $srv->setRdata(Factory::SRV(10, 10, 21, 'files'));

        $zone->fromList($loc, $mx2, $srv);
        $zone->addResourceRecord($soa);
        $zone->addResourceRecord($ns1);
        $zone->addResourceRecord($mx3);
        $zone->addResourceRecord($a);
        $zone->addResourceRecord($a6);
        $zone->addResourceRecord($ns2);
        $zone->addResourceRecord($mx1);

        $apl = new APL();
        $apl->addAddressRange(IPBlock::create('192.168.0.0/23'));
        $apl->addAddressRange(IPBlock::create('192.168.1.64/28'), false);
        $apl->addAddressRange(IPBlock::create('2001:acad:1::/112'), true);
        $apl->addAddressRange(IPBlock::create('2001:acad:1::8/128'), false);

        $multicast = ResourceRecord::create('multicast', $apl);

        $zone->addResourceRecord($multicast);

        return $zone;
    }

    public function testSetName(): void
    {
        $zone = new Zone();
        $zone->setName('example.com.');
        $this->assertEquals('example.com.', $zone->getName());

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Zone "example.com" is not a fully qualified domain name.');
        $zone->setName('example.com');
    }

    public function testFillOut(): void
    {
        $zone = self::buildTestZone();
        $alignedBuilder = new AlignedBuilder();

        ZoneBuilder::fillOutZone($zone);
        $expectation = file_get_contents(__DIR__.'/Resources/example.com_filled-out.txt');

        //This is a fix for Windows systems that may expect a carriage return char.
        $expectation = str_replace("\r", '', $expectation);

        $this->assertEquals($expectation, $alignedBuilder->build($zone));
    }

    public function testOtherFunctions(): void
    {
        $zone = TestZone::buildTestZone();
        $this->assertCount(15, $zone);
        $this->assertFalse($zone->isEmpty());

        $rr = $zone->getResourceRecords()[0];
        $this->assertTrue($zone->contains($rr));
        $this->assertTrue($zone->remove($rr));
        $this->assertFalse($zone->remove($rr));
        $this->assertFalse($zone->contains($rr));

        //Test Zone:offsetSet()
        $this->assertArrayNotHasKey(0, $zone);
        $zone[0] = $rr;
        $this->assertArrayHasKey(0, $zone);
    }

    public function testGetClassReturnsDefaultClass(): void
    {
        $a = Factory::A('192.168.1.1');
        $h1 = ResourceRecord::create('host1', $a, 3600);
        $h1->setClass(null);

        $h2 = ResourceRecord::create('host2', $a, 3600);
        $h2->setClass(null);

        $h3 = ResourceRecord::create('host3', $a, 3600);
        $h3->setClass(null);

        $zone = new Zone('example.com.');
        $zone->fromList($h1, $h2, $h3);

        $this->assertNull($h1->getClass());
        $this->assertNull($h2->getClass());
        $this->assertNull($h3->getClass());

        $this->assertEquals(Classes::INTERNET, $zone->getClass());
    }

    public function testArrayAccess(): void
    {
        $zone = TestZone::buildTestZone();
        $this->assertInstanceOf(ResourceRecord::class, $zone[3]);
        $this->assertEquals('SOA', $zone[0]->getType());
        unset($zone[0]);
        $this->assertArrayNotHasKey(0, $zone);
        $this->assertTrue(isset($zone[1]));
    }
}
