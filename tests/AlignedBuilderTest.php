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
use Badcow\DNS\Rdata\Factory;
use Badcow\DNS\ResourceRecord;
use PHPUnit\Framework\TestCase;

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
ipv6domain       3600  IN AAAA  ::1; This is an IPv6 domain.

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
two.cities             IN TXT   ( 
                                  "It was the best of times, it was the wor"
                                  "st of times, it was the age of wisdom, i"
                                  "t was the age of foolishness, it was the"
                                  " epoch of belief, it was the epoch of in"
                                  "credulity, it was the season of Light, i"
                                  "t was the season of Darkness, it was the"
                                  " spring of hope, it was the winter of de"
                                  "spair, we had everything before us, we h"
                                  "ad nothing before us, we were all going "
                                  "direct to Heaven, we were all going dire"
                                  "ct the other way—in short, the period "
                                  "was so far like the present period, that"
                                  " some of its noisiest authorities insist"
                                  "ed on its being received, for good or fo"
                                  "r evil, in the superlative degree of com"
                                  "parison only."
                                )

; SRV RECORDS
_ftp._tcp              IN SRV   10 10 21 files

; RRSIG RECORDS
example.com.           IN RRSIG A 14 2 3600 (
                                20200112073532 20191229133101 12345 example.com.
                                bDts/7a5qbal6s3ZYzS5puPSjEfys5yI
                                6R/kprBBRDEfVcT6YwPaDT3VkVjKXdvp
                                KX2/DwpijNAWkjpfsewCLmeImx3Rgkzf
                                uxfipRKtBUguiPTBhkj/ft2halJziVXl )

DNS;

    protected function getExpected(): string
    {
        //This is a fix for Windows systems that may expect a carriage return char.
        return str_replace("\r", '', $this->expected);
    }

    public function testCompareResourceRecords(): void
    {
        $soa = new ResourceRecord();
        $soa->setClass('IN');
        $soa->setName('@');
        $soa->setRdata(Factory::SOA(
            'example.com.',
            'postmaster.example.com.',
            2015050801,
            3600,
            14400,
            604800,
            3600
        ));

        $ns1 = new ResourceRecord();
        $ns1->setClass(Classes::INTERNET);
        $ns1->setName('@');
        $ns1->setTtl(14400);
        $ns1->setRdata(Factory::NS('ns1.example.net.au.'));

        $ns2 = new ResourceRecord();
        $ns2->setClass('IN');
        $ns2->setName('@');
        $ns2->setTtl(14400);
        $ns2->setRdata(Factory::NS('ns2.example.net.au.'));

        $a = new ResourceRecord();
        $a->setName('subdomain.au');
        $a->setRdata(Factory::A('192.168.1.2'));
        $a->setComment('This is a local ip.');

        $cname = new ResourceRecord();
        $cname->setName('alias');
        $cname->setRdata(Factory::CNAME('subdomain.au.example.com.'));
        $cname->setClass(Classes::INTERNET);

        $aaaa = ResourceRecord::create('ipv6domain', Factory::AAAA('::1'), 3600);

        $mx1 = new ResourceRecord();
        $mx1->setName('@');
        $mx1->setRdata(Factory::MX(10, 'mailgw01.example.net.'));

        $mx2 = new ResourceRecord();
        $mx2->setName('@');
        $mx2->setRdata(Factory::MX(20, 'mailgw02.example.net.'));

        $txt = new ResourceRecord();
        $txt->setName('example.net.');
        $txt->setRdata(Factory::TXT('v=spf1 ip4:192.0.2.0/24 ip4:198.51.100.123 a -all'));

        $spf = new ResourceRecord();
        $spf->setName('example.com.');
        $spf->setRdata(Factory::SPF('v=spf1 ip4:192.0.2.0/24 ip4:198.51.100.123 a -all'));

        $rrsig = new ResourceRecord();
        $rrsig->setName('example.com.');
        $rrsig->setRdata(Factory::RRSIG(
            'A',
            14,
            2,
            3600,
            new \DateTime('2070-01-01 00:00:00'),
            new \DateTime('1970-01-01 00:00:00'),
            65535,
            'example.com.',
            'aaaaaaaaaaaaaaaaaaaaaaaaa'
        ));

        $nsec3 = new ResourceRecord();
        $nsec3->setName('example.com.');
        $nsec3->setRdata(Factory::NSEC3PARAM(1, 0, 5, '9474017E'));

        $rp = new ResourceRecord();
        $rp->setRdata(Factory::RP('mail.example.com.', 'example.com.'));

        $spf = new ResourceRecord();
        $spf->setRdata(Factory::SPF('skjdfskjasdfjh'));

        $alignedBuilder = new AlignedBuilder();

        $this->assertTrue($alignedBuilder->compareResourceRecords($soa, $ns1) < 0);
        $this->assertTrue($alignedBuilder->compareResourceRecords($aaaa, $cname) < 0);
        $this->assertTrue($alignedBuilder->compareResourceRecords($mx1, $mx2) < 0);
        $this->assertTrue($alignedBuilder->compareResourceRecords($mx1, $mx2) < 0);
        $this->assertTrue($alignedBuilder->compareResourceRecords($mx1, $spf) < 0);

        $this->assertTrue($alignedBuilder->compareResourceRecords($mx1, $a) > 0);
        $this->assertTrue($alignedBuilder->compareResourceRecords($ns2, $ns1) > 0);
        $this->assertTrue($alignedBuilder->compareResourceRecords($spf, $txt) > 0);

        $this->assertTrue($alignedBuilder->compareResourceRecords($nsec3, $rrsig) < 0);
        $this->assertTrue($alignedBuilder->compareResourceRecords($rrsig, $nsec3) > 0);

        $this->assertTrue($alignedBuilder->compareResourceRecords($rp, $spf) < 0);
    }

    public function testBuild(): void
    {
        $zone = TestZone::buildTestZone();
        $twoCities = new ResourceRecord();
        $twoCities->setName('two.cities');
        $twoCities->setRdata(Factory::TXT('It was the best of times, it was the worst of times, it was the age of wisdom, it was the age of foolishness, it was the epoch of belief, it was the epoch of incredulity, it was the season of Light, it was the season of Darkness, it was the spring of hope, it was the winter of despair, we had everything before us, we had nothing before us, we were all going direct to Heaven, we were all going direct the other way—in short, the period was so far like the present period, that some of its noisiest authorities insisted on its being received, for good or for evil, in the superlative degree of comparison only.'));

        $zone->addResourceRecord($twoCities);

        $resourceRecord = new ResourceRecord();
        $resourceRecord->setName('null');
        $zone->addResourceRecord($resourceRecord);

        $builder = new AlignedBuilder();
        $output = $builder->build($zone);

        $this->assertEquals($this->getExpected(), $output);
    }
}
