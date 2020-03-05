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

use Badcow\DNS\Classes;
use Badcow\DNS\Rdata\Factory;
use Badcow\DNS\Rdata\NS;
use Badcow\DNS\ResourceRecord;
use Badcow\DNS\Validator;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    public function dp_testValidateResourceRecordName(): array
    {
        return [
            [true, 'example.com.'],
            [true, 'www.example.com.'],
            [true, 'ex-ample.com.'],
            [true, 'ex-ampl3.com.au.'],
            [true, '@'],
            [true, 'alt2.aspmx.l.google.com.'],
            [true, 'www.eXAMple.cOm.'],
            [true, '3xample.com.'],
            [true, '_example.com.'],
            [true, 'example.com'],
            [true, 'www.example.com'],
            [true, 'example'],
            [true, '@'],
            [true, 'wWw.EXample.com'],
            [true, '_sip._udp.test.sx.'],
            [false, '-example.com.'],
            [false, 'exam*ple.com'],
            [false, 'wheres-wally?.com'],
        ];
    }

    /**
     * @dataProvider dp_testValidateResourceRecordName
     *
     * @param bool   $isValid
     * @param string $resourceName
     */
    public function testValidateResourceRecordName(bool $isValid, string $resourceName): void
    {
        $this->assertEquals($isValid, Validator::resourceRecordName($resourceName));
    }

    public function testValidateIpv4Address(): void
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

        $this->assertTrue(Validator::ipv4($valid1));
        $this->assertTrue(Validator::ipv4($valid2));
        $this->assertTrue(Validator::ipv4($valid3));
        $this->assertTrue(Validator::ipv4($valid4));

        $this->assertFalse(Validator::ipv4($invalid1));
        $this->assertFalse(Validator::ipv4($invalid2));
        $this->assertFalse(Validator::ipv4($invalid3));
        $this->assertFalse(Validator::ipv4($invalid4));
        $this->assertFalse(Validator::ipv4($invalid5));
    }

    public function testValidateIpv6Address(): void
    {
        $valid1 = '2001:0db8:0000:0000:0000:ff00:0042:8329';
        $valid2 = '2001:db8:0:0:0:ff00:42:8329';
        $valid3 = '2001:db8::ff00:42:8329';
        $valid4 = '::1';

        $invalid1 = 'fffff:0db8:0000:0000:0000:ff00:0042:8329';
        $invalid2 = '172.10.255.1';
        $invalid3 = '192.168.0.0';

        $this->assertTrue(Validator::ipv6($valid1));
        $this->assertTrue(Validator::ipv6($valid2));
        $this->assertTrue(Validator::ipv6($valid3));
        $this->assertTrue(Validator::ipv6($valid4));

        $this->assertFalse(Validator::ipv6($invalid1));
        $this->assertFalse(Validator::ipv6($invalid2));
        $this->assertFalse(Validator::ipv6($invalid3));
    }

    public function testValidateIpAddress(): void
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

        $this->assertTrue(Validator::ipAddress($valid1));
        $this->assertTrue(Validator::ipAddress($valid2));
        $this->assertTrue(Validator::ipAddress($valid3));
        $this->assertTrue(Validator::ipAddress($valid4));
        $this->assertTrue(Validator::ipAddress($valid5));
        $this->assertTrue(Validator::ipAddress($valid6));
        $this->assertTrue(Validator::ipAddress($valid7));
        $this->assertTrue(Validator::ipAddress($valid8));

        $this->assertFalse(Validator::ipAddress($invalid1));
        $this->assertFalse(Validator::ipAddress($invalid2));
        $this->assertFalse(Validator::ipAddress($invalid3));
        $this->assertFalse(Validator::ipAddress($invalid4));
    }

    public function testValidateNumberOfSoa(): void
    {
        $zone = TestZone::buildTestZone();
        $soa = new ResourceRecord();
        $soa->setClass(Classes::INTERNET);
        $soa->setName('@');
        $soa->setRdata(Factory::SOA(
            'example.com.',
            'postmaster.example.com.',
            (int) date('Ymd01'),
            3600,
            14400,
            604800,
            3600
        ));
        $zone->addResourceRecord($soa);

        $this->assertEquals(Validator::ZONE_TOO_MANY_SOA, Validator::zone($zone));
    }

    public function testValidateNumberOfClasses(): void
    {
        $zone = TestZone::buildTestZone();
        $a = new ResourceRecord();
        $a->setName('test');
        $a->setClass(Classes::CHAOS);
        $a->setRdata(Factory::A('192.168.0.1'));
        $a->setComment('This class does not belong here');
        $zone->addResourceRecord($a);

        $this->assertEquals(Validator::ZONE_TOO_MANY_CLASSES, Validator::zone($zone));
    }

    public function testValidateZone(): void
    {
        $zone = TestZone::buildTestZone();

        //Remove the NS records.
        foreach ($zone as $resourceRecord) {
            if (NS::TYPE === $resourceRecord->getType()) {
                $zone->remove($resourceRecord);
            }
        }

        $soa = new ResourceRecord();
        $soa->setClass(Classes::INTERNET);
        $soa->setName('@');
        $soa->setRdata(Factory::SOA(
            'example.com.',
            'postmaster.example.com.',
            (int) date('Ymd01'),
            3600,
            14400,
            604800,
            3600
        ));
        $a = new ResourceRecord();
        $a->setName('test');
        $a->setClass(Classes::CHAOS);
        $a->setRdata(Factory::A('192.168.0.1'));
        $a->setComment('This class does not belong here');

        $zone->addResourceRecord($a);
        $zone->addResourceRecord($soa);

        $this->assertTrue((bool) (Validator::ZONE_TOO_MANY_CLASSES & Validator::zone($zone)));
        $this->assertTrue((bool) (Validator::ZONE_NO_NS & Validator::zone($zone)));
        $expectation = Validator::ZONE_NO_NS | Validator::ZONE_TOO_MANY_CLASSES | Validator::ZONE_TOO_MANY_SOA;
        $this->assertEquals($expectation, Validator::zone($zone));
    }

    public function testZone(): void
    {
        $zone = TestZone::buildTestZone();
        $this->assertEquals(Validator::ZONE_OKAY, Validator::zone($zone));
    }

    public function testWildcard(): void
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

        $this->assertTrue(Validator::resourceRecordName($valid_1));
        $this->assertTrue(Validator::resourceRecordName($valid_2));
        $this->assertTrue(Validator::resourceRecordName($valid_3));
        $this->assertTrue(Validator::resourceRecordName($valid_4));
        $this->assertTrue(Validator::resourceRecordName($valid_5));

        $this->assertFalse(Validator::resourceRecordName($invalid_1));
        $this->assertFalse(Validator::resourceRecordName($invalid_2));
        $this->assertFalse(Validator::resourceRecordName($invalid_3));
        $this->assertFalse(Validator::resourceRecordName($invalid_4));
    }

    public function testReverseIpv4(): void
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

    public function testReverseIpv6(): void
    {
        $valid_01 = 'b.a.9.8.7.6.5.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.8.b.d.0.1.0.0.2.ip6.arpa.';
        $valid_02 = '1.0.0.0.6.8.7.0.6.5.a.0.4.0.5.1.2.0.0.3.8.f.0.1.0.0.2.ip6.arpa.';
        $invalid_01 = 'b.a.9.8.7.6.5.0.0.g.0.0.0.0.0.0.0.0.0.0.0.0.0.0.8.b.d.0.1.0.0.2.ip6.arpa.';

        $this->assertTrue(Validator::reverseIpv6($valid_01));
        $this->assertTrue(Validator::reverseIpv6($valid_02));
        $this->assertFalse(Validator::reverseIpv6($invalid_01));

        $this->assertTrue(Validator::fullyQualifiedDomainName($valid_01));
        $this->assertTrue(Validator::fullyQualifiedDomainName($valid_02));
    }

    public function testResourceRecordName(): void
    {
        $case_1 = '*.';
        $case_2 = '*.hello.com';
        $case_3 = 'www.*.hello.com';

        $this->assertFalse(Validator::resourceRecordName($case_1));
        $this->assertTrue(Validator::resourceRecordName($case_2));
        $this->assertFalse(Validator::resourceRecordName($case_3));
    }

    public function testFqdn(): void
    {
        //Pass cases
        $fqdn1 = 'example.com.';
        $fqdn2 = 'www.example.com.';
        $fqdn3 = 'ex-ample.com.';
        $fqdn4 = 'ex-ampl3.com.au.';
        $fqdn5 = 'alt2.aspmx.l.google.com.';
        $fqdn6 = 'www.eXAMple.cOm.';
        $fqdn7 = '3xample.com.';

        //Fail cases
        $fqdn8 = '_example.com.';
        $fqdn9 = '-example.com.';
        $fqdn10 = 'example.com';
        $fqdn11 = 'e&ample.com.';

        $this->assertTrue(Validator::fullyQualifiedDomainName($fqdn1));
        $this->assertTrue(Validator::fullyQualifiedDomainName($fqdn2));
        $this->assertTrue(Validator::fullyQualifiedDomainName($fqdn3));
        $this->assertTrue(Validator::fullyQualifiedDomainName($fqdn4));
        $this->assertTrue(Validator::fullyQualifiedDomainName($fqdn5));
        $this->assertTrue(Validator::fullyQualifiedDomainName($fqdn6));
        $this->assertTrue(Validator::fullyQualifiedDomainName($fqdn7));

        $this->assertFalse(Validator::fullyQualifiedDomainName($fqdn8));
        $this->assertFalse(Validator::fullyQualifiedDomainName($fqdn9));
        $this->assertFalse(Validator::fullyQualifiedDomainName($fqdn10));
        $this->assertFalse(Validator::fullyQualifiedDomainName($fqdn11));
    }

    public function testHostName(): void
    {
        $this->assertTrue(Validator::hostName('ya-hoo123'));
    }

    public function testNoAliasInZone(): void
    {
        //Pass case
        $txt1 = new ResourceRecord();
        $txt1->setName('www');
        $txt1->setRdata(Factory::TXT('v=spf1 ip4:192.0.2.0/24 ip4:198.51.100.123 a -all'));
        $txt1->setClass(Classes::INTERNET);

        //Fail case
        $txt2 = new ResourceRecord();
        $txt2->setName('alias');
        $txt2->setRdata(Factory::TXT('v=spf1 ip4:192.0.2.0/24 ip4:198.51.100.123 a -all'));
        $txt2->setClass(Classes::INTERNET);

        $zone = TestZone::buildTestZone();

        $this->assertTrue(Validator::noAliasInZone($zone, $txt1));

        $this->assertFalse(Validator::noAliasInZone($zone, $txt2));
    }

    public function testIsUnsignedInteger(): void
    {
        $this->assertFalse(Validator::isUnsignedInteger(-1, 16));
        $this->assertFalse(Validator::isUnsignedInteger(65536, 16));
        $this->assertTrue(Validator::isUnsignedInteger(65535, 16));
        $this->assertTrue(Validator::isUnsignedInteger(0, 16));

        $this->expectException(\RuntimeException::class);
        Validator::isUnsignedInteger(10, 64);
    }

    public function testIsBase32Encoded(): void
    {
        $this->assertTrue(Validator::isBase32Encoded('JBSWY3DPFQQHI2DJOMQGS4ZAMEQGEYLTMUZTEIDFNZRW6ZDFMQQHG5DSNFXGOLQ='));
        $this->assertFalse(Validator::isBase32Encoded('JBSWY3DPFQQHI2DJOMQGS8ZAMEQGEYLTMUZTEIDFNZRW6ZDFMQQHG5DSNFXGOLQ='));
        $this->assertFalse(Validator::isBase32Encoded('JBSWY3DPFQQHI2DJOMQGS8ZAMEQGEYLTMUZTEIDFNZRW6ZDFMQQHG5DSNFXGOLQ='));
    }

    public function testIsBase64Encoded(): void
    {
        $this->assertTrue(Validator::isBase64Encoded('VGhpcyBpcyBhIGJhc2U2NCBlbmNvZGVkIHN0cmluZy4='));
        $this->assertFalse(Validator::isBase64Encoded('VGhpcyBpcyBhIGJhc2U2NCBlbmNvZGVkIHN0cmluZy4=='));
        $this->assertFalse(Validator::isBase64Encoded('VGhpcyBpcyBhIGJhc2U2NCBlbmNvZGV\IHN0cmluZy4='));
    }
}
