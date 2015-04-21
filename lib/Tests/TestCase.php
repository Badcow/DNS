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

use Badcow\DNS\ResourceRecord,
    Badcow\DNS\Rdata\Factory,
    Badcow\DNS\Classes,
    Badcow\DNS\Zone;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * Get an environment variable
     *
     * @param string $varname
     * @return mixed
     */
    protected function getEnvVariable($varname)
    {
        if (false !== $var = getenv($varname)) {
            return $var;
        }

        return null;
    }

    /**
     * @return Zone
     */
    protected function buildTestZone()
    {
        $soa = new ResourceRecord;
        $soa->setClass('IN');
        $soa->setName('@');
        $soa->setRdata(Factory::Soa(
            'example.com.',
            'postmaster.example.com.',
            date('Ymd01'),
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

        $a_record = new ResourceRecord;
        $a_record->setName('subdomain.au');
        $a_record->setRdata(Factory::A('192.168.1.2'));
        $a_record->setComment("This is a local ip.");

        $cname = new ResourceRecord;
        $cname->setName('alias');
        $cname->setRdata(Factory::Cname('subdomain.au.example.com.'));

        $aaaa = new ResourceRecord;
        $aaaa->setName('ipv6domain');
        $aaaa->setRdata(Factory::Aaaa('::1'));
        $aaaa->setComment("This is an IPv6 domain.");

        $mx = new ResourceRecord;
        $mx->setName('@');
        $mx->setRdata(Factory::Mx(10, 'mail.example.net.'));

        $txt = new ResourceRecord;
        $txt->setName('example.net.');
        $txt->setRdata(Factory::txt('v=spf1 ip4:192.0.2.0/24 ip4:198.51.100.123 a -all'));

        $loc = new ResourceRecord;
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

        $dname = new ResourceRecord;
        $dname->setName('bar.example.com.');
        $dname->setClass(Classes::INTERNET);
        $dname->setRdata(Factory::Dname('foo.example.com.'));

        $hinfo = new ResourceRecord;
        $hinfo->setName('@');
        $hinfo->setClass(Classes::INTERNET);
        $hinfo->setRdata(Factory::Hinfo('2.7GHz', 'Ubuntu 12.04'));

        return new Zone('example.com.', 3600, array(
            $soa,
            $ns1,
            $ns2,
            $a_record,
            $aaaa,
            $loc,
            $dname,
            $mx,
            $cname,
            $txt,
            $hinfo,
        ));
    }
}
 