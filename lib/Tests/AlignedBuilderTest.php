<?php
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
use Badcow\DNS\ResourceRecord;
use Badcow\DNS\Rdata\Factory;
use Badcow\DNS\Tests\Rdata\DummyRdata;

class AlignedBuilderTest extends TestCase
{
    protected $expected = <<< 'DNS'
$ORIGIN example.com.
$TTL 3600
@                      IN SOA   (
                                example.com.            ; MNAME
                                postmaster.example.com. ; RNAME
                                2015050801              ; SERIAL
                                3600                    ; REFRESH
                                14400                   ; RETRY
                                604800                  ; EXPIRE
                                3600                    ; MINIMUM
                                )

; NS RECORDS
@                14400 IN NS    ns1.example.net.au.
@                14400 IN NS    ns2.example.net.au.

; A RECORDS
subdomain.au           IN A     192.168.1.2; This is a local ip.

; AAAA RECORDS
ipv6domain             IN AAAA  ::1; This is an IPv6 domain.

; CNAME RECORDS
alias                  IN CNAME subdomain.au.example.com.

; DNAME RECORDS
bar.example.com.       IN DNAME foo.example.com.

; MX RECORDS
@                      IN MX    10 mail-gw1.example.net.
@                      IN MX    20 mail-gw2.example.net.
@                      IN MX    30 mail-gw3.example.net.

; LOC RECORDS
canberra               IN LOC   (
                                35 18 27.000 S ; LATITUDE
                                149 7 27.840 E ; LONGITUDE
                                500.00m        ; ALTITUDE
                                20.12m         ; SIZE
                                200.30m        ; HORIZONTAL PRECISION
                                300.10m        ; VERTICAL PRECISION
                                ); This is Canberra

; HINFO RECORDS
@                      IN HINFO "2.7GHz" "Ubuntu 12.04"

; TXT RECORDS
example.net.           IN TXT   "v=spf1 ip4:192.0.2.0/24 ip4:198.51.100.123 a -all"

DNS;

    public function testCompareResourceRecords()
    {
        $soa = new ResourceRecord;
        $soa->setClass('IN');
        $soa->setName('@');
        $soa->setRdata(Factory::Soa(
            'example.com.',
            'postmaster.example.com.',
            2015050801,
            3600,
            14400,
            604800,
            3600
        ));

        $ns1 = new ResourceRecord;
        $ns1->setClass('IN');
        $ns1->setName('@');
        $ns1->setTtl(14400);
        $ns1->setRdata(Factory::Ns('ns1.example.net.au.'));

        $ns2 = new ResourceRecord;
        $ns2->setClass('IN');
        $ns2->setName('@');
        $ns2->setTtl(14400);
        $ns2->setRdata(Factory::Ns('ns2.example.net.au.'));

        $a = new ResourceRecord;
        $a->setName('subdomain.au');
        $a->setRdata(Factory::A('192.168.1.2'));
        $a->setComment("This is a local ip.");

        $cname = new ResourceRecord;
        $cname->setName('alias');
        $cname->setRdata(Factory::Cname('subdomain.au.example.com.'));

        $aaaa = new ResourceRecord;
        $aaaa->setName('ipv6domain');
        $aaaa->setRdata(Factory::Aaaa('::1'));
        $aaaa->setComment("This is an IPv6 domain.");

        $mx1 = new ResourceRecord;
        $mx1->setName('@');
        $mx1->setRdata(Factory::Mx(10, 'mailgw01.example.net.'));

        $mx2 = new ResourceRecord;
        $mx2->setName('@');
        $mx2->setRdata(Factory::Mx(20, 'mailgw02.example.net.'));

        $txt = new ResourceRecord;
        $txt->setName('example.net.');
        $txt->setRdata(Factory::txt('v=spf1 ip4:192.0.2.0/24 ip4:198.51.100.123 a -all'));

        $dummy = new ResourceRecord;
        $dummy->setName('example.com.');
        $dummy->setRdata(new DummyRdata());

        $this->assertTrue(AlignedBuilder::compareResourceRecords($soa, $ns1) < 0);
        $this->assertTrue(AlignedBuilder::compareResourceRecords($ns2, $ns1) > 0);
        $this->assertTrue(AlignedBuilder::compareResourceRecords($aaaa, $cname) < 0);
        $this->assertTrue(AlignedBuilder::compareResourceRecords($mx1, $a) > 0);
        $this->assertTrue(AlignedBuilder::compareResourceRecords($mx1, $mx2) < 0);
        $this->assertTrue(AlignedBuilder::compareResourceRecords($mx1, $mx2) < 0);
        $this->assertTrue(AlignedBuilder::compareResourceRecords($dummy, $txt) > 0);
        $this->assertTrue(AlignedBuilder::compareResourceRecords($mx1, $dummy) < 0);
    }

    public function testBuild()
    {
        $zone = $this->buildTestZone();
        $builder = new AlignedBuilder();
        $zoneFile = $builder->build($zone);
        if (true == $this->getEnvVariable(self::PHP_ENV_PRINT_TEST_ZONE)) {
            $this->printBlock($zoneFile, 'ALIGNED TEST ZONE');
        }

        $this->assertEquals($this->expected, $zoneFile);
    }

    public function testZoneFile()
    {
        $this->bindTest($this->buildTestZone(), new AlignedBuilder);
    }
}
