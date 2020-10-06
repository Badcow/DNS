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

use Badcow\DNS\Algorithms;
use Badcow\DNS\Classes;
use Badcow\DNS\Rdata\A;
use Badcow\DNS\Rdata\Factory;
use Badcow\DNS\Rdata\RRSIG;
use Badcow\DNS\ResourceRecord;
use Badcow\DNS\Zone;

final class TestZone
{
    /**
     * @var string
     */
    public static $expected = <<< 'DNS'
$ORIGIN example.com.
$TTL 3600
@ IN SOA example.com. postmaster.example.com. 2015050801 3600 14400 604800 3600
@ 14400 IN NS ns1.example.net.au.
@ 14400 IN NS ns2.example.net.au.
subdomain.au IN A 192.168.1.2; This is a local ip.
ipv6domain 3600 IN AAAA ::1; This is an IPv6 domain.
canberra IN LOC 35 18 27.000 S 149 7 27.840 E 500.00m 20.12m 200.30m 300.10m; This is Canberra
bar.example.com. IN DNAME foo.example.com.
@ IN MX 30 mail-gw3.example.net.
@ IN MX 10 mail-gw1.example.net.
@ IN MX 20 mail-gw2.example.net.
alias IN CNAME subdomain.au.example.com.
example.net. IN TXT "v=spf1 ip4:192.0.2.0/24 ip4:198.51.100.123 a -all"
@ IN HINFO "2.7GHz" "Ubuntu 12.04"
_ftp._tcp IN SRV 10 10 21 files
example.com. IN RRSIG A 14 2 3600 20200112073532 20191229133101 12345 example.com. bDts/7a5qbal6s3ZYzS5puPSjEfys5yI6R/kprBBRDEfVcT6YwPaDT3VkVjKXdvpKX2/DwpijNAWkjpfsewCLmeImx3RgkzfuxfipRKtBUguiPTBhkj/ft2halJziVXl

DNS;

    private function __construct()
    {
    }

    public static function getExpectation(): string
    {
        return str_replace("\r", '', self::$expected);
    }

    public static function buildTestZone(): Zone
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
        $ns1->setClass('IN');
        $ns1->setName('@');
        $ns1->setTtl(14400);
        $ns1->setRdata(Factory::NS('ns1.example.net.au.'));

        $ns2 = new ResourceRecord();
        $ns2->setClass('IN');
        $ns2->setName('@');
        $ns2->setTtl(14400);
        $ns2->setRdata(Factory::NS('ns2.example.net.au.'));

        $a_record = new ResourceRecord();
        $a_record->setName('subdomain.au');
        $a_record->setRdata(Factory::A('192.168.1.2'));
        $a_record->setComment('This is a local ip.');

        $cname = new ResourceRecord();
        $cname->setName('alias');
        $cname->setRdata(Factory::CNAME('subdomain.au.example.com.'));

        $aaaa = ResourceRecord::create(
            'ipv6domain',
            Factory::AAAA('::1'),
            3600,
            Classes::INTERNET,
            'This is an IPv6 domain.'
        );

        $mx1 = new ResourceRecord();
        $mx1->setName('@');
        $mx1->setRdata(Factory::MX(10, 'mail-gw1.example.net.'));

        $mx2 = new ResourceRecord();
        $mx2->setName('@');
        $mx2->setRdata(Factory::MX(20, 'mail-gw2.example.net.'));

        $mx3 = new ResourceRecord();
        $mx3->setName('@');
        $mx3->setRdata(Factory::MX(30, 'mail-gw3.example.net.'));

        $txt = new ResourceRecord();
        $txt->setName('example.net.');
        $txt->setRdata(Factory::TXT('v=spf1 ip4:192.0.2.0/24 ip4:198.51.100.123 a -all'));
        $txt->setClass(Classes::INTERNET);

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
        $loc->setClass(Classes::INTERNET);

        $dname = new ResourceRecord();
        $dname->setName('bar.example.com.');
        $dname->setClass(Classes::INTERNET);
        $dname->setRdata(Factory::Dname('foo.example.com.'));

        $hinfo = new ResourceRecord();
        $hinfo->setName('@');
        $hinfo->setClass(Classes::INTERNET);
        $hinfo->setRdata(Factory::HINFO('2.7GHz', 'Ubuntu 12.04'));

        $srv = new ResourceRecord();
        $srv->setName('_ftp._tcp');
        $srv->setClass('IN');
        $srv->setRdata(Factory::SRV(10, 10, 21, 'files'));

        $rrsig = new ResourceRecord();
        $rrsig->setName('example.com.');
        $rrsig->setRdata(Factory::RRSIG(
            A::TYPE,
            Algorithms::ECDSAP384SHA384,
            2,
            3600,
            \DateTime::createFromFormat(RRSIG::TIME_FORMAT, '20200112073532'),
            \DateTime::createFromFormat(RRSIG::TIME_FORMAT, '20191229133101'),
            12345,
            'example.com.',
            base64_decode('bDts/7a5qbal6s3ZYzS5puPSjEfys5yI6R/kprBBRDEfVcT6YwPaDT3VkVjKXdvpKX2/DwpijNAWkjpfsewCLmeImx3RgkzfuxfipRKtBUguiPTBhkj/ft2halJziVXl')
        ));

        return new Zone('example.com.', 3600, [
            $soa,
            $ns1,
            $ns2,
            $a_record,
            $aaaa,
            $loc,
            $dname,
            $mx3,
            $mx1,
            $mx2,
            $cname,
            $txt,
            $hinfo,
            $srv,
            $rrsig,
        ]);
    }
}
