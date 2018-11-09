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

use Badcow\DNS\Classes;
use Badcow\DNS\Validator;
use Badcow\DNS\Rdata\Factory;
use Badcow\DNS\ResourceRecord;
use Badcow\DNS\Zone;

class ValidatorTest extends TestCase
{
    public function testValidateFqdn()
    {
        //Pass cases
        $fqdn1 = 'example.com.';
        $fqdn2 = 'www.example.com.';
        $fqdn3 = 'ex-ample.com.';
        $fqdn4 = 'ex-ampl3.com.au.';
        $fqdn5 = '@';
        $fqdn6 = 'alt2.aspmx.l.google.com.';
        $fqdn7 = 'www.eXAMple.cOm.';

        //Fail cases
        $fqdn8 = '3xample.com.';
        $fqdn9 = '_example.com.';
        $fqdn10 = '-example.com.';

        //Pass cases
        $uqdn1 = 'example.com';
        $uqdn2 = 'www.example.com';
        $uqdn3 = 'example';
        $uqdn4 = '@';
        $uqdn5 = 'wWw.EXample.com';

        //Fail cases
        $uqdn6 = 'exam*ple.com';
        $uqdn7 = 'wheres-wally?.com';
        $uqdn8 = '_example.com.';
        $uqdn9 = '-example.com.';
        $uqdn10 = '3xample.com.';

        $this->assertTrue(Validator::validateFqdn($fqdn1));
        $this->assertTrue(Validator::validateFqdn($fqdn2));
        $this->assertTrue(Validator::validateFqdn($fqdn3));
        $this->assertTrue(Validator::validateFqdn($fqdn4));
        $this->assertTrue(Validator::validateFqdn($fqdn5));
        $this->assertTrue(Validator::validateFqdn($fqdn6));
        $this->assertTrue(Validator::validateFqdn($fqdn7));

        $this->assertFalse(Validator::validateFqdn($fqdn8));
        $this->assertFalse(Validator::validateFqdn($fqdn9));
        $this->assertFalse(Validator::validateFqdn($fqdn10));

        $this->assertFalse(Validator::validateFqdn($uqdn1));
        $this->assertFalse(Validator::validateFqdn($uqdn2));
        $this->assertFalse(Validator::validateFqdn($uqdn3));
        $this->assertFalse(Validator::validateFqdn($uqdn5));

        $this->assertTrue(Validator::validateFqdn($uqdn1, false));
        $this->assertTrue(Validator::validateFqdn($uqdn2, false));
        $this->assertTrue(Validator::validateFqdn($uqdn3, false));
        $this->assertTrue(Validator::validateFqdn($uqdn4, false));
        $this->assertTrue(Validator::validateFqdn($uqdn5, false));

        $this->assertFalse(Validator::validateFqdn($uqdn6, false));
        $this->assertFalse(Validator::validateFqdn($uqdn7, false));
        $this->assertFalse(Validator::validateFqdn($uqdn8, false));
        $this->assertFalse(Validator::validateFqdn($uqdn9, false));
        $this->assertFalse(Validator::validateFqdn($uqdn10, false));
    }

    public function testValidateIpv4Address()
    {
        $valid1 = '119.15.101.102';
        $valid2 = '255.0.0.255';
        $valid3 = '192.168.0.0';
        $valid4 = '0.0.0.0';

        $invalid1 = '192.168.1.';
        $invalid2 = '172.10.256.1';
        $invalid3 = '255.244';
        $invalid4 = '::1';
        $invalid5 = '2001:db8::ff00:42:8329';

        $this->assertTrue(Validator::validateIpv4Address($valid1));
        $this->assertTrue(Validator::validateIpv4Address($valid2));
        $this->assertTrue(Validator::validateIpv4Address($valid3));
        $this->assertTrue(Validator::validateIpv4Address($valid4));

        $this->assertFalse(Validator::validateIpv4Address($invalid1));
        $this->assertFalse(Validator::validateIpv4Address($invalid2));
        $this->assertFalse(Validator::validateIpv4Address($invalid3));
        $this->assertFalse(Validator::validateIpv4Address($invalid4));
        $this->assertFalse(Validator::validateIpv4Address($invalid5));
    }

    public function testValidateIpv6Address()
    {
        $valid1 = '2001:0db8:0000:0000:0000:ff00:0042:8329';
        $valid2 = '2001:db8:0:0:0:ff00:42:8329';
        $valid3 = '2001:db8::ff00:42:8329';
        $valid4 = '::1';

        $invalid1 = 'fffff:0db8:0000:0000:0000:ff00:0042:8329';
        $invalid2 = '172.10.255.1';
        $invalid3 = '192.168.0.0';

        $this->assertTrue(Validator::validateIpv6Address($valid1));
        $this->assertTrue(Validator::validateIpv6Address($valid2));
        $this->assertTrue(Validator::validateIpv6Address($valid3));
        $this->assertTrue(Validator::validateIpv6Address($valid4));

        $this->assertFalse(Validator::validateIpv6Address($invalid1));
        $this->assertFalse(Validator::validateIpv6Address($invalid2));
        $this->assertFalse(Validator::validateIpv6Address($invalid3));
    }

    public function testValidateIpAddress()
    {
        $valid1 = '2001:0db8:0000:0000:0000:ff00:0042:8329';
        $valid2 = '2001:db8:0:0:0:ff00:42:8329';
        $valid3 = '2001:db8::ff00:42:8329';
        $valid4 = '::1';
        $valid5 = '119.15.101.102';
        $valid6 = '255.0.0.255';
        $valid7 = '192.168.0.0';
        $valid8 = '0.0.0.0';

        $invalid1 = '192.168.1.';
        $invalid2 = '172.10.256.1';
        $invalid3 = '255.244';
        $invalid4 = 'fffff:0db8:0000:0000:0000:ff00:0042:8329';

        $this->assertTrue(Validator::validateIpAddress($valid1));
        $this->assertTrue(Validator::validateIpAddress($valid2));
        $this->assertTrue(Validator::validateIpAddress($valid3));
        $this->assertTrue(Validator::validateIpAddress($valid4));
        $this->assertTrue(Validator::validateIpAddress($valid5));
        $this->assertTrue(Validator::validateIpAddress($valid6));
        $this->assertTrue(Validator::validateIpAddress($valid7));
        $this->assertTrue(Validator::validateIpAddress($valid8));

        $this->assertFalse(Validator::validateIpAddress($invalid1));
        $this->assertFalse(Validator::validateIpAddress($invalid2));
        $this->assertFalse(Validator::validateIpAddress($invalid3));
        $this->assertFalse(Validator::validateIpAddress($invalid4));
    }

    /**
     * @expectedExceptionMessage There must be exactly one SOA record, 2 given.
     */
    public function testValidateNumberOfSoa()
    {
        $zone = $this->buildTestZone();
        $soa = new ResourceRecord();
        $soa->setClass(Classes::INTERNET);
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
        $zone->addResourceRecord($soa);

        $this->assertEquals(Validator::ZONE_TOO_MANY_SOA, Validator::zone($zone));
    }

    /**
     * @expectedExceptionMessage There must be at least one NS record, 0 given.
     */
    public function testValidateNumberOfNs()
    {
        $zone = new Zone('example.com.', 3600);
        $soa = new ResourceRecord();
        $soa->setClass(Classes::INTERNET);
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
        $zone->addResourceRecord($soa);

        $this->assertEquals(Validator::ZONE_NO_NS, Validator::zone($zone));
    }

    /**
     * @expectedExceptionMessage There must be exactly one type of class, 2 given.
     */
    public function testValidateNumberOfClasses()
    {
        $zone = $this->buildTestZone();
        $a = new ResourceRecord();
        $a->setName('test');
        $a->setClass(Classes::CHAOS);
        $a->setRdata(Factory::A('192.168.0.1'));
        $a->setComment('This class does not belong here');
        $zone->addResourceRecord($a);

        $this->assertEquals(Validator::ZONE_TOO_MANY_CLASSES, Validator::zone($zone));
    }

    public function testZone()
    {
        $zone = $this->buildTestZone();
        $this->assertEquals(Validator::ZONE_OKAY, Validator::zone($zone));
    }

    public function testWildcard()
    {
        $valid_1 = '*.example.com.';
        $valid_2 = '*';
        $valid_3 = '*.sub';
        $valid_4 = '*.sub.domain';
        $valid_5 = '*.sub.example.com.';

        $invalid_1 = '*abc.example.com.';
        $invalid_2 = 'domain.*.example.com.';
        $invalid_3 = 'example.com.*';
        $invalid_4 = '*.';

        $this->assertTrue(Validator::validateFqdn($valid_1, true));
        $this->assertTrue(Validator::validateFqdn($valid_2, false));
        $this->assertTrue(Validator::validateFqdn($valid_3, false));
        $this->assertTrue(Validator::validateFqdn($valid_4, false));
        $this->assertTrue(Validator::validateFqdn($valid_5, true));

        $this->assertFalse(Validator::validateFqdn($invalid_1, true));
        $this->assertFalse(Validator::validateFqdn($invalid_2, true));
        $this->assertFalse(Validator::validateFqdn($invalid_3, false));
        $this->assertFalse(Validator::validateFqdn($invalid_4, true));
    }

    public function testReverseIpv4()
    {
        $valid_01 = '10.IN-ADDR.ARPA.';
        $valid_02 = '10.IN-ADDR.ARPA.';
        $valid_03 = '18.IN-addr.ARPA.';
        $valid_04 = '26.IN-ADdr.ArpA.';
        $valid_05 = '22.0.2.10.IN-ADDR.ARPA.';
        $valid_06 = '103.0.0.26.IN-ADDR.ARPA.';
        $valid_07 = '77.0.0.10.IN-ADDR.ARPA.';
        $valid_08 = '4.0.10.18.IN-ADDR.ARPA.';
        $valid_09 = '103.0.3.26.IN-ADDR.ARPA.';
        $valid_10 = '6.0.0.10.IN-ADDR.ARPA.';

        $invalid_01 = '10.IN-ADDR.ARPA';
        $invalid_02 = '10.20.ARPA.';
        $invalid_03 = '10.123.0.1.INADDR.ARPA.';
        $invalid_04 = '10.1.1.1.1.in-addr.arpa.';
        $invalid_05 = '10.1.256.7.in-addr.arpa.';

        $this->assertTrue(Validator::reverseIpv4($valid_01));
        $this->assertTrue(Validator::reverseIpv4($valid_02));
        $this->assertTrue(Validator::reverseIpv4($valid_03));
        $this->assertTrue(Validator::reverseIpv4($valid_04));
        $this->assertTrue(Validator::reverseIpv4($valid_05));
        $this->assertTrue(Validator::reverseIpv4($valid_06));
        $this->assertTrue(Validator::reverseIpv4($valid_07));
        $this->assertTrue(Validator::reverseIpv4($valid_08));
        $this->assertTrue(Validator::reverseIpv4($valid_09));
        $this->assertTrue(Validator::reverseIpv4($valid_10));

        $this->assertFalse(Validator::reverseIpv4($invalid_01));
        $this->assertFalse(Validator::reverseIpv4($invalid_02));
        $this->assertFalse(Validator::reverseIpv4($invalid_03));
        $this->assertFalse(Validator::reverseIpv4($invalid_04));
        $this->assertFalse(Validator::reverseIpv4($invalid_05));
    }

    public function testReverseIpv6()
    {
        $valid_01 = 'b.a.9.8.7.6.5.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.8.b.d.0.1.0.0.2.ip6.arpa.';
        $valid_02 = '1.0.0.0.6.8.7.0.6.5.a.0.4.0.5.1.2.0.0.3.8.f.0.1.0.0.2.ip6.arpa.';
        $invalid_01 = 'b.a.9.8.7.6.5.0.0.g.0.0.0.0.0.0.0.0.0.0.0.0.0.0.8.b.d.0.1.0.0.2.ip6.arpa.';

        $this->assertTrue(Validator::reverseIpv6($valid_01));
        $this->assertTrue(Validator::reverseIpv6($valid_02));
        $this->assertFalse(Validator::reverseIpv6($invalid_01));
    }

    public function testRrName()
    {
        $case_1 = '*.';
        $case_2 = '*.hello.com';
        $case_3 = 'www.*.hello.com';

        $this->assertFalse(Validator::rrName($case_1));
        $this->assertTrue(Validator::rrName($case_2));
        $this->assertFalse(Validator::rrName($case_3));
    }

    public function testFqdn()
    {
        //Pass cases
        $fqdn1 = 'example.com.';
        $fqdn2 = 'www.example.com.';
        $fqdn3 = 'ex-ample.com.';
        $fqdn4 = 'ex-ampl3.com.au.';
        $fqdn5 = 'alt2.aspmx.l.google.com.';
        $fqdn6 = 'www.eXAMple.cOm.';

        //Fail cases
        $fqdn7 = '3xample.com.';
        $fqdn8 = '_example.com.';
        $fqdn9 = '-example.com.';
        $fqdn10 = 'example.com';
        $fqdn11 = 'e&ample.com.';

        $this->assertTrue(Validator::fqdn($fqdn1));
        $this->assertTrue(Validator::fqdn($fqdn2));
        $this->assertTrue(Validator::fqdn($fqdn3));
        $this->assertTrue(Validator::fqdn($fqdn4));
        $this->assertTrue(Validator::fqdn($fqdn5));
        $this->assertTrue(Validator::fqdn($fqdn6));

        $this->assertFalse(Validator::fqdn($fqdn7));
        $this->assertFalse(Validator::fqdn($fqdn8));
        $this->assertFalse(Validator::fqdn($fqdn9));
        $this->assertFalse(Validator::fqdn($fqdn10));
        $this->assertFalse(Validator::fqdn($fqdn11));
    }
}
