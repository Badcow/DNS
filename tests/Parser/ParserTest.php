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

namespace Badcow\DNS\Tests\Parser;

use Badcow\DNS\Algorithms;
use Badcow\DNS\AlignedBuilder;
use Badcow\DNS\Classes;
use Badcow\DNS\Parser\Comments;
use Badcow\DNS\Parser\ParseException;
use Badcow\DNS\Parser\Parser;
use Badcow\DNS\Parser\TimeFormat;
use Badcow\DNS\Parser\ZoneFileFetcherInterface;
use Badcow\DNS\Rdata\A;
use Badcow\DNS\Rdata\AAAA;
use Badcow\DNS\Rdata\APL;
use Badcow\DNS\Rdata\CAA;
use Badcow\DNS\Rdata\CNAME;
use Badcow\DNS\Rdata\Factory;
use Badcow\DNS\Rdata\RRSIG;
use Badcow\DNS\Rdata\TXT;
use Badcow\DNS\ResourceRecord;
use Badcow\DNS\Zone;
use Badcow\DNS\ZoneBuilder;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{
    /**
     * Build a test zone.
     */
    private function getTestZone(): Zone
    {
        $zone = new Zone('example.com.');
        $zone->setDefaultTtl(3600);

        $soa = new ResourceRecord();
        $soa->setName('@');
        $soa->setRdata(Factory::SOA(
            'example.com.',
            'post.example.com.',
            2014110501,
            3600,
            14400,
            604800,
            3600
        ));

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

        $dname = ResourceRecord::create('hq', Factory::Dname('syd.example.com.'));

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
     * @throws ParseException
     */
    public function testParserCreatesValidDnsObject(): void
    {
        $zoneBuilder = new AlignedBuilder();
        $zone = $zoneBuilder->build($this->getTestZone());

        $expectation = $this->getTestZone();
        foreach ($expectation->getResourceRecords() as $rr) {
            $rr->setTtl($rr->getTtl() ?? $expectation->getDefaultTtl());
        }

        $actual = Parser::parse('example.com.', $zone, Comments::END_OF_ENTRY);

        $this->assertEquals($expectation, $actual);
    }

    /**
     * Parser ignores control entries other than TTL.
     *
     * @throws ParseException|\Exception
     */
    public function testParserIgnoresControlEntriesOtherThanTtl(): void
    {
        $file = NormaliserTest::readFile(__DIR__.'/Resources/testCollapseMultilines_sample.txt');
        $zone = Parser::parse('example.com.', $file);

        $this->assertEquals('example.com.', $zone->getName());
        $this->assertEquals('::1', self::findRecord('ipv6.domain', $zone)[0]->getRdata()->getAddress());
        $this->assertEquals(1337, $zone->getDefaultTtl());
    }

    /**
     * Parser can handle convoluted zone record.
     *
     * @throws ParseException|\Exception
     */
    public function testParserCanHandleConvolutedZoneRecord(): void
    {
        $file = NormaliserTest::readFile(__DIR__.'/Resources/testConvolutedZone_sample.txt');
        $zone = Parser::parse('example.com.', $file);
        $this->assertEquals(3600, $zone->getDefaultTtl());
        $this->assertCount(28, $zone->getResourceRecords());

        $txt = ResourceRecord::create(
            'testtxt',
            Factory::TXT('v=DKIM1; k=rsa; p=MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBg'.
                'QDZKI3U+9acu3NfEy0NJHIPydxnPLPpnAJ7k2JdrsLqAK1uouMudHI20pgE8RMldB/TeW'.
                'KXYoRidcGCZWXleUzldDTwZAMDQNpdH1uuxym0VhoZpPbI1RXwpgHRTbCk49VqlC'),
            600,
            Classes::INTERNET
        );

        $txt2 = 'Some text another Some text';

        $this->assertEquals($txt, self::findRecord($txt->getName(), $zone)[0]);
        $this->assertEquals($txt2, self::findRecord('test', $zone)[0]->getRdata()->getText());
        $this->assertCount(1, self::findRecord('xn----7sbfndkfpirgcajeli2a4pnc.xn----7sbbfcqfo2cfcagacemif0ap5q', $zone));
        $this->assertCount(4, self::findRecord('testmx', $zone));
    }

    /**
     * @throws ParseException
     */
    public function testCanHandlePolymorphicRdata(): void
    {
        $string = 'example.com. 7200 IN A6 2001:acad::1337; This is invalid.';
        $zone = Parser::parse('example.com.', $string);
        $rr = $zone->getResourceRecords()[0];

        $rdata = $rr->getRdata();

        $this->assertNotNull($rdata);

        if (null === $rdata) {
            return;
        }

        $this->assertEquals('A6', $rdata->getType());
        $this->assertEquals('2001:acad::1337', $rdata->toText());
    }

    /**
     * @throws ParseException|\Exception
     */
    public function testParserCanHandleAplRecords(): void
    {
        $file = NormaliserTest::readFile(__DIR__.'/Resources/testCollapseMultilines_sample.txt');
        $zone = Parser::parse('example.com.', $file);

        /** @var APL $apl */
        $apl = self::findRecord('multicast', $zone)[0]->getRdata();
        $this->assertCount(2, $apl->getIncludedAddressRanges());
        $this->assertCount(2, $apl->getExcludedAddressRanges());

        $this->assertEquals('192.168.0.0/23', (string) $apl->getIncludedAddressRanges()[0]);
        $this->assertEquals('2001:acad:1::8/128', (string) $apl->getExcludedAddressRanges()[1]);
    }

    /**
     * @throws ParseException
     */
    public function testParserCanHandleCaaRecords(): void
    {
        $text = <<<'TXT'
$ORIGIN EXAMPLE.COM.
$TTL 3600
@ 10800 IN CAA 0 issue "letsencrypt.org"
TXT;

        $zone = Parser::parse('example.com.', $text);
        $this->assertCount(1, $zone);
        /** @var CAA $caa */
        $caa = $zone->getResourceRecords()[0]->getRdata();

        $this->assertEquals('CAA', $caa->getType());
        $this->assertEquals(0, $caa->getFlag());
        $this->assertEquals('issue', $caa->getTag());
        $this->assertEquals('letsencrypt.org', $caa->getValue());
    }

    /**
     * @throws ParseException
     */
    public function testParserCanHandleSshfpRecords(): void
    {
        $txt = 'host.example. IN SSHFP 2 1 123456789abcdef67890123456789abcdef67890';
        $zone = Parser::parse('example.', $txt);

        $rrs = self::findRecord('host.example.', $zone, 'SSHFP');
        $sshfp = $rrs[0]->getRdata();

        $this->assertEquals(2, $sshfp->getAlgorithm());
        $this->assertEquals(1, $sshfp->getFingerprintType());
        $this->assertEquals(hex2bin('123456789abcdef67890123456789abcdef67890'), $sshfp->getFingerprint());
    }

    /**
     * @throws ParseException
     */
    public function testParserCanHandleUriRecords(): void
    {
        $txt = '_ftp._tcp    IN URI 10 1 "ftp://ftp1.example.com/public%20data"';
        $zone = Parser::parse('example.com.', $txt);

        $rrs = self::findRecord('_ftp._tcp', $zone, 'URI');
        $uri = $rrs[0]->getRdata();

        $this->assertEquals(10, $uri->getPriority());
        $this->assertEquals(1, $uri->getWeight());
        $this->assertEquals('ftp://ftp1.example.com/public%20data', $uri->getTarget());
    }

    /**
     * @throws ParseException
     */
    public function testMalformedAplRecordThrowsException1(): void
    {
        $zone = 'multicast 3600 IN APL 3:192.168.0.64/30';

        $this->expectException(ParseException::class);

        Parser::parse('example.com.', $zone);
    }

    /**
     * @throws ParseException
     */
    public function testUnknownRdataTypeThrowsException(): void
    {
        $zone = 'resource 3600 IN XX f080:3024:a::1';

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Could not parse entry "resource 3600 IN XX f080:3024:a::1".');

        Parser::parse('acme.com.', $zone);
    }

    /**
     * @throws ParseException
     */
    public function testMalformedAplRecordThrowsException2(): void
    {
        $zone = 'multicast 3600 IN APL !1-192.168.0.64/30';

        $this->expectException(ParseException::class);

        Parser::parse('example.com.', $zone);
    }

    /**
     * @throws \Exception|ParseException
     */
    public function testAmbiguousRecordsParse(): void
    {
        $file = NormaliserTest::readFile(__DIR__.'/Resources/ambiguous.acme.org.txt');
        $zone = Parser::parse('ambiguous.acme.org.', $file);
        $mxRecords = self::findRecord('mx', $zone);
        $a4Records = self::findRecord('aaaa', $zone);

        $this->assertCount(3, $mxRecords);
        $this->assertCount(2, $a4Records);
        foreach ($mxRecords as $rr) {
            switch ($rr->getType()) {
                case A::TYPE:
                    $this->assertEquals(900, $rr->getTtl());
                    $this->assertEquals('200.100.50.35', $rr->getRdata()->getAddress());
                    break;
                case CNAME::TYPE:
                    $this->assertEquals(3600, $rr->getTtl());
                    $this->assertEquals('aaaa', $rr->getRdata()->getTarget());
                    break;
                case TXT::TYPE:
                    $this->assertEquals(3600, $rr->getTtl());
                    $this->assertEquals('Mail Exchange IPv6 Address', $rr->getRdata()->getText());
                    break;
            }
        }

        foreach ($a4Records as $rr) {
            switch ($rr->getType()) {
                case AAAA::TYPE:
                    $this->assertEquals(900, $rr->getTtl());
                    $this->assertEquals('2001:acdc:5889::35', $rr->getRdata()->getAddress());
                    break;
                case TXT::TYPE:
                    $this->assertEquals(3600, $rr->getTtl());
                    $this->assertEquals('This name is silly.', $rr->getRdata()->getText());
                    break;
            }
        }
    }

    /**
     * @throws ParseException
     */
    public function testAmbiguousRecord(): void
    {
        $record = 'mx cname aaaa';
        $zone = Parser::parse('acme.com.', $record);
        $mx = $zone->getResourceRecords()[0];

        $this->assertEquals(CNAME::TYPE, $mx->getType());
        $this->assertEquals('mx', $mx->getName());
        $this->assertEquals('aaaa', $mx->getRdata()->getTarget());
    }

    /**
     * @throws ParseException
     */
    public function testUnknownRdataTypesAreParsed(): void
    {
        $entries = <<<DNS
a.example.com.   CLASS32     TYPE731         \# 6 abcd   ef 01 23 45
b.example.com.   HS          TYPE62347       \# 0
c.example.com.   IN          A               \# 4 0A000001
d.example.com.   CLASS1      TYPE1           \# 4 0A 00 00 02
DNS;

        $zone = Parser::parse('example.com.', $entries);
        $this->assertCount(4, $zone);

        $a = self::findRecord('a.example.com.', $zone)[0];
        $this->assertEquals(731, $a->getRdata()->getTypeCode());
        $this->assertEquals(hex2bin('abcdef012345'), $a->getRdata()->getData());
        $this->assertEquals('CLASS32', $a->getClass());

        $b = self::findRecord('b.example.com.', $zone)[0];
        $this->assertEquals(62347, $b->getRdata()->getTypeCode());
        $this->assertEquals(null, $b->getRdata()->getData());

        $c = self::findRecord('c.example.com.', $zone)[0];
        $this->assertInstanceOf(A::class, $c->getRdata());
        $this->assertEquals('10.0.0.1', $c->getRdata()->getAddress());

        $d = self::findRecord('d.example.com.', $zone)[0];
        $this->assertInstanceOf(A::class, $d->getRdata());
        $this->assertEquals('10.0.0.2', $d->getRdata()->getAddress());
    }

    /**
     * @throws ParseException
     */
    public function testParserRecognisesHumanReadableTimeFormats(): void
    {
        $record = <<<DNS
\$TTL 1h1m3s
badcow.co.     1h5m IN SOA   ns.badcow.co. hostmaster.badcow.co. (
                             2020070101 ; serial
                             3h10s      ; refresh
                             59m        ; retry
                             4w1d       ; expire
                             1h         ; minimum
                             )
overflow      615000000w IN A     4.3.2.1
numeric       12345 IN A     9.9.9.9
DNS;
        $zone = Parser::parse('badcow.co.', $record);
        $this->assertEquals(3663, $zone->getDefaultTtl());
        $this->assertCount(3, $zone);

        $this->assertEquals(3900, $zone[0]->getTtl());
        $soa = $zone[0]->getRdata();
        $this->assertEquals(10810, $soa->getRefresh());
        $this->assertEquals(3540, $soa->getRetry());
        $this->assertEquals(2505600, $soa->getExpire());
        $this->assertEquals(3600, $soa->getMinimum());

        $this->assertEquals(0, $zone[1]->getTtl());
        $this->assertEquals(12345, $zone[2]->getTtl());

        // Ensure coverage
        $this->assertEquals('1w4d13h46m39s', TimeFormat::toHumanReadable(999999));
        $this->assertEquals('1h', TimeFormat::toHumanReadable(3600));
    }

    /**
     * @throws ParseException
     */
    public function testParserRecognisesResourceNameOnRrsigRecords(): void
    {
        $record = <<<DNS
dns.badcow.co. 3600 IN SOA   ns.badcow.co. hostmaster.badcow.co. (
                             2020010101 ; serial
                             10800      ; refresh (3 hours)
                             3600       ; retry (1 hour)
                             2419200    ; expire (4 weeks)
                             3600       ; minimum (1 hour)
                             )
               3600    RRSIG A 4 2 86400 (
                             20050322173103 20030220173103 2642 example.com.
                             oJB1W6WNGv+ldvQ3WDG0MQkg5IEhjRip
                             8WTrPYGv07h108dUKGMeDPKijVCHX3DD
                             Kdfb+v6oB9wfuh3DTJXUAfI/M0zmO/zz
                             8bW0Rznl8O3tGNazPwQKkRN20XPXV6nw
                             wfoXmJQbsLNrLfkGJ5D6fwFm8nN+6pBz
                             eDQfsS3Ap3o= )
DNS;

        $expectedSignature = base64_decode('oJB1W6WNGv+ldvQ3WDG0MQkg5IEhjRip8WTrPYGv07h108dUKGMeDPKijVCHX3DDKdfb+v6oB9wfuh3DTJXUAfI/'.
            'M0zmO/zz8bW0Rznl8O3tGNazPwQKkRN20XPXV6nwwfoXmJQbsLNrLfkGJ5D6fwFm8nN+6pBzeDQfsS3Ap3o=');
        $expectedExpiration = \DateTime::createFromFormat(RRSIG::TIME_FORMAT, '20050322173103');
        $expectedInception = \DateTime::createFromFormat(RRSIG::TIME_FORMAT, '20030220173103');

        $zone = Parser::parse('badcow.co.', $record);

        $this->assertCount(2, $zone);
        /** @var ResourceRecord $rr */
        $rr = $zone[1];

        $this->assertEquals('dns.badcow.co.', $rr->getName());
        $this->assertEquals(3600, $rr->getTtl());
        /** @var RRSIG $rrsig */
        $rrsig = $rr->getRdata();
        $this->assertInstanceOf(RRSIG::class, $rrsig);
        $this->assertEquals('A', $rrsig->getTypeCovered());
        $this->assertEquals(Algorithms::ECC, $rrsig->getAlgorithm());
        $this->assertEquals(2, $rrsig->getLabels());
        $this->assertEquals(86400, $rrsig->getOriginalTtl());
        $this->assertEquals($expectedExpiration, $rrsig->getSignatureExpiration());
        $this->assertEquals($expectedInception, $rrsig->getSignatureInception());
        $this->assertEquals(2642, $rrsig->getKeyTag());
        $this->assertEquals('example.com.', $rrsig->getSignersName());
        $this->assertEquals($expectedSignature, $rrsig->getSignature());
    }

    /**
     * Tests if a control entry on a zone file will overwrite the initial parameter in Parser::parse().
     *
     * @throws \Exception
     */
    public function testParserDoesNotOverwritesZoneNameIfOriginControlEntryIsDifferent(): void
    {
        $file = NormaliserTest::readFile(__DIR__.'/Resources/testCollapseMultilines_sample.txt');
        $zone = Parser::parse('test.com.', $file);

        $this->assertEquals('test.com.', $zone->getName());
    }

    /**
     * Find all records in a Zone named $name.
     *
     * @return ResourceRecord[]
     */
    public static function findRecord(?string $name, Zone $zone, ?string $type = 'ANY'): array
    {
        $records = [];

        foreach ($zone->getResourceRecords() as $resourceRecord) {
            if ($name === $resourceRecord->getName() && ('ANY' === $type || $type === $resourceRecord->getType())) {
                $records[] = $resourceRecord;
            }
        }

        return $records;
    }

    /**
     * Parser handles multiple $ORIGINS.
     *
     * @throws ParseException|\Exception
     */
    public function testParserHandlesMultipleOrigins(): void
    {
        $file = NormaliserTest::readFile(__DIR__.'/Resources/multipleOrigins.txt');
        $expectation = NormaliserTest::readFile(__DIR__.'/Resources/multipleOrigins_expectation.txt');
        $zone = Parser::parse('mydomain.biz.', $file);

        $this->assertEquals('mydomain.biz.', $zone->getName());
        $this->assertEquals(3600, $zone->getDefaultTtl());

        $this->assertEquals($expectation, ZoneBuilder::build($zone));
    }

    /**
     * Parser handles empty names with multiple $ORIGINS.
     *
     * @throws ParseException|\Exception
     */
    public function testParserHandlesEmptyNamesWithMultipleOrigins(): void
    {
        $file = NormaliserTest::readFile(__DIR__.'/Resources/testEmptyNamesWithMultipleOrigins.txt');
        $expectation = NormaliserTest::readFile(__DIR__.'/Resources/testEmptyNamesWithMultipleOrigins_expectation.txt');
        $zone = Parser::parse('mydomain.biz.', $file);

        $this->assertEquals($expectation, ZoneBuilder::build($zone));
    }

    /**
     * Parser handles $ORIGIN . correctly.
     *
     * @throws ParseException|\Exception
     */
    public function testParserHandlesOriginDot(): void
    {
        $file = NormaliserTest::readFile(__DIR__.'/Resources/testOriginDot_sample.txt');
        $expectation = NormaliserTest::readfile(__DIR__.'/Resources/testOriginDot_expectation.txt');

        $zone = Parser::parse('otherdomain.biz.', $file);
        $this->assertEquals('otherdomain.biz.', $zone->getName());

        ZoneBuilder::fillOutZone($zone);
        $this->assertEquals($expectation, ZoneBuilder::build($zone));
    }

    public function dp_testParserHandlesIncludeDirective(): array
    {
        $baseDir = __DIR__.'/Resources/IncludeControlEntryTests/';

        return [
            ['mydomain.biz.', 3600, $baseDir.'mydomain.biz.db', $baseDir.'mydomain.biz_expectation.db', Comments::ALL],
            ['testdomain.geek.', 7200, $baseDir.'testdomain.geek.db', $baseDir.'testdomain.geek_expectation.db', Comments::NONE],
        ];
    }

    /**
     * Parser imports files specified by the $INCLUDE directive.
     *
     * @dataProvider dp_testParserHandlesIncludeDirective
     *
     * @throws ParseException|\Exception
     */
    public function testParserHandlesIncludeDirective(string $zoneName, int $ttl, string $zoneFilePath, string $expectationPath, int $commentOptions): void
    {
        $zoneFetcher = new class() implements ZoneFileFetcherInterface {
            public function fetch(string $path): string
            {
                return file_get_contents(__DIR__.'/Resources/IncludeControlEntryTests/'.$path);
            }
        };

        $file = NormaliserTest::readFile($zoneFilePath);
        $expectation = NormaliserTest::readFile($expectationPath);
        $zone = (new Parser([], $zoneFetcher))->makeZone($zoneName, $file, $commentOptions);

        $this->assertEquals($zoneName, $zone->getName());
        $this->assertEquals($ttl, $zone->getDefaultTtl());

        $this->assertEquals($expectation, ZoneBuilder::build($zone));
    }

    public function testIssue89(): void
    {
        $zone = Parser::parse('tld.', "\$ORIGIN tld.\nzone.tld. 900 IN TXT 3600");

        $this->assertCount(1, $zone);
        $rr = $zone[0];

        $this->assertEquals('zone.tld.', $rr->getName());
        $this->assertEquals(900, $rr->getTtl());
        $this->assertEquals(Classes::INTERNET, $rr->getClass());
        $this->assertEquals('3600', $rr->getRdata()->getText());
    }
}
