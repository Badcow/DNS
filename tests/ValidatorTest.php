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
use RuntimeException;

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
     */
    public function testValidateResourceRecordName(bool $isValid, string $resourceName): void
    {
        $this->assertEquals($isValid, Validator::resourceRecordName($resourceName));
    }

    public function getIPv4TestDataSet(): array
    {
        return [
            ['119.15.101.102', true],
            ['255.0.0.255', true],
            ['192.168.0.0', true],
            ['0.0.0.0', true],

            ['192.168.1.', false],
            ['172.10.256.1', false],
            ['255.244', false],
            ['::1', false],
            ['2001:db8::ff00:42:8329', false],
        ];
    }

    /**
     * @dataProvider getIPv4TestDataSet
     */
    public function testValidateIpv4Address(string $address, bool $isValid): void
    {
        $this->assertEquals($isValid, Validator::ipv4($address));
    }

    public function getIPv6TestDataSet(): array
    {
        return [
            ['2001:0db8:0000:0000:0000:ff00:0042:8329', true],
            ['2001:db8:0:0:0:ff00:42:8329', true],
            ['2001:db8::ff00:42:8329', true],
            ['::1', true],

            ['fffff:0db8:0000:0000:0000:ff00:0042:8329', false],
            ['172.10.255.1', false],
            ['192.168.0.0', false],
        ];
    }

    /**
     * @dataProvider getIPv6TestDataSet
     */
    public function testValidateIpv6Address(string $address, bool $isValid): void
    {
        $this->assertEquals($isValid, Validator::ipv6($address));
    }

    public function getIPvTestDataSet(): array
    {
        return [
            ['2001:0db8:0000:0000:0000:ff00:0042:8329', true],
            ['2001:db8:0:0:0:ff00:42:8329', true],
            ['2001:db8::ff00:42:8329', true],
            ['::1', true],
            ['119.15.101.102', true],
            ['255.0.0.255', true],
            ['192.168.0.0', true],
            ['0.0.0.0', true],

            ['192.168.1.', false],
            ['172.10.256.1', false],
            ['255.244', false],
            ['fffff:0db8:0000:0000:0000:ff00:0042:8329', false],
        ];
    }

    /**
     * @dataProvider getIPvTestDataSet
     */
    public function testValidateIpAddress(string $address, bool $isValid): void
    {
        $this->assertEquals($isValid, Validator::ipAddress($address));
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

    public function getWildcardTestData(): array
    {
        return [
            ['*.example.com.', true],
            ['*', true],
            ['*.sub', true],
            ['*.sub.domain', true],
            ['*.sub.example.com.', true],

            ['*abc.example.com.', false],
            ['domain.*.example.com.', false],
            ['example.com.*', false],
            ['*.', false],
        ];
    }

    /**
     * @param string $name    the wildcard domain to be validated
     * @param bool   $isValid whether the domain is valid
     *
     * @dataProvider getWildcardTestData
     */
    public function testWildcard(string $name, bool $isValid): void
    {
        $this->assertEquals($isValid, Validator::resourceRecordName($name));
    }

    public function getTestReverseIpv4DataProvider(): array
    {
        return [
            ['10.IN-ADDR.ARPA.', true],
            ['10.IN-ADDR.ARPA.', true],
            ['18.IN-addr.ARPA.', true],
            ['26.IN-ADdr.ArpA.', true],
            ['22.0.2.10.IN-ADDR.ARPA.', true],
            ['103.0.0.26.IN-ADDR.ARPA.', true],
            ['77.0.0.10.IN-ADDR.ARPA.', true],
            ['4.0.10.18.IN-ADDR.ARPA.', true],
            ['103.0.3.26.IN-ADDR.ARPA.', true],
            ['6.0.0.10.IN-ADDR.ARPA.', true],

            ['10.IN-ADDR.ARPA', false],
            ['10.20.ARPA.', false],
            ['10.123.0.1.INADDR.ARPA.', false],
            ['10.1.1.1.1.in-addr.arpa.', false],
            ['10.1.256.7.in-addr.arpa.', false],
        ];
    }

    /**
     * @dataProvider getTestReverseIpv4DataProvider
     */
    public function testReverseIpv4(string $ptr, bool $isValid): void
    {
        $this->assertEquals($isValid, Validator::reverseIpv4($ptr));
    }

    public function getTestReverseIpv6DataProvider(): array
    {
        return [
            ['b.a.9.8.7.6.5.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.8.b.d.0.1.0.0.2.ip6.arpa.', true],
            ['1.0.0.0.6.8.7.0.6.5.a.0.4.0.5.1.2.0.0.3.8.f.0.1.0.0.2.ip6.arpa.', true],
            ['b.a.9.8.7.6.5.0.0.g.0.0.0.0.0.0.0.0.0.0.0.0.0.0.8.b.d.0.1.0.0.2.ip6.arpa.', false],
        ];
    }

    /**
     * @dataProvider getTestReverseIpv6DataProvider
     */
    public function testReverseIpv6(string $ptr, bool $isValid): void
    {
        $this->assertEquals($isValid, Validator::reverseIpv6($ptr));
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

    public function getTestFqdnDataProvider(): array
    {
        return [
            ['example.com.', true],
            ['www.example.com.', true],
            ['ex-ample.com.', true],
            ['ex-ampl3.com.au.', true],
            ['alt2.aspmx.l.google.com.', true],
            ['www.eXAMple.cOm.', true],
            ['3xample.com.', true],
            ['_sip._tcp.example.com.', true, false],
            ['_sip._tcp.example.com.', false, true],
            ['_example.com.', false],
            ['-example.com.', false],
            ['example.com', false],
            ['e&ample.com.', false],
        ];
    }

    /**
     * @dataProvider getTestFqdnDataProvider
     */
    public function testFqdn(string $domain, bool $isValid, bool $strictHostValidation = true): void
    {
        $this->assertEquals($isValid, Validator::fullyQualifiedDomainName($domain, $strictHostValidation));
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

        $this->expectException(RuntimeException::class);
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
