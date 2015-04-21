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
use Badcow\DNS\Validator,
    Badcow\DNS\Rdata\Factory,
    Badcow\DNS\ResourceRecord,
    Badcow\DNS\Zone;

class ValidatorTest extends TestCase
{
    public function testIsValidClass()
    {
        $this->assertTrue(Validator::isValidClass('IN'));
        $this->assertTrue(Validator::isValidClass('HS'));
        $this->assertTrue(Validator::isValidClass('CH'));

        $this->assertFalse(Validator::isValidClass('INTERNET'));
        $this->assertFalse(Validator::isValidClass('in'));
        $this->assertFalse(Validator::isValidClass('In'));
        $this->assertFalse(Validator::isValidClass('hS'));
    }

    public function testValidateFqdn()
    {
        $fqdn1 = 'example.com.';
        $fqdn2 = 'www.example.com.';
        $fqdn3 = 'ex-ample.com.';
        $fqdn4 = 'ex-ampl3.com.au.';
        $fqdn5 = '3xample.com.';

        $uqdn1 = 'example.com';
        $uqdn2 = 'www.example.com';
        $uqdn3 = 'example';

        $this->assertTrue(Validator::validateFqdn($fqdn1));
        $this->assertTrue(Validator::validateFqdn($fqdn2));
        $this->assertTrue(Validator::validateFqdn($fqdn3));
        $this->assertTrue(Validator::validateFqdn($fqdn4));
        $this->assertTrue(Validator::validateFqdn($fqdn5));

        $this->assertFalse(Validator::validateFqdn($uqdn1));
        $this->assertFalse(Validator::validateFqdn($uqdn2));
        $this->assertFalse(Validator::validateFqdn($uqdn3));

        $this->assertTrue(Validator::validateFqdn($uqdn1, false));
        $this->assertTrue(Validator::validateFqdn($uqdn2, false));
        $this->assertTrue(Validator::validateFqdn($uqdn3, false));
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
     * @expectedException \Badcow\DNS\ZoneException
     * @expectedExceptionMessage There must be exactly one SOA record, 2 given.
     */
    public function testValidateNumberOfSoa()
    {
        $zone = $this->buildTestZone();
        $soa = new ResourceRecord;
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
        Validator::validate($zone);
    }

    /**
     * @expectedException \Badcow\DNS\ZoneException
     * @expectedExceptionMessage There must be at least one NS record, 0 given.
     */
    public function testValidateNumberOfNs()
    {
        $zone = new Zone('example.com.', 3600);
        $soa = new ResourceRecord;
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
        Validator::validate($zone);
    }

    /**
     * @expectedException \Badcow\DNS\ZoneException
     * @expectedExceptionMessage There must be exactly one type of class, 2 given.
     */
    public function testValidateNumberOfClasses()
    {
        $zone = $this->buildTestZone();
        $a = new ResourceRecord;
        $a->setName('test');
        $a->setClass(Classes::CHAOS);
        $a->setRdata(Factory::A('192.168.0.1'));
        $a->setComment('This class does not belong here');
        $zone->addResourceRecord($a);

        Validator::validate($zone);
    }
}
