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

use Badcow\Common\TempFile,
    Badcow\DNS\Zone,
    Badcow\DNS\ResourceRecord,
    Badcow\DNS\Rdata\SoaRdata,
    Badcow\DNS\Rdata\NsRdata,
    Badcow\DNS\Rdata\ARdata,
    Badcow\DNS\Rdata\AaaaRdata,
    Badcow\DNS\Validator,
    Badcow\DNS\ZoneBuilder,
    Badcow\DNS\Classes,
    Badcow\DNS\Rdata\Factory;

class ZoneFileTest extends TestCase
{
    const PHP_ENV_CHECKZONE_PATH = 'CHECKZONE_PATH';
    const PHP_ENV_PRINT_TEST_ZONE = 'PRINT_TEST_ZONE';

    private function buildTestZone()
    {
        $soa_rdata = new SoaRdata;
        $soa_rdata->setMname('example.com.');
        $soa_rdata->setRname('postmaster.example.com.');
        $soa_rdata->setSerial(date('Ymd01'));
        $soa_rdata->setRefresh(3600);
        $soa_rdata->setExpire(14400);
        $soa_rdata->setRetry(604800);
        $soa_rdata->setMinimum(3600);

        $soa = new ResourceRecord;
        $soa->setClass('IN');
        $soa->setName('@');
        $soa->setRdata($soa_rdata);

        $ns1r = new NsRdata;
        $ns1r->setNsdname('ns1.example.net.au.');

        $ns2r = new NsRdata;
        $ns2r->setNsdname('ns2.example.net.au.');

        $ns1 = new ResourceRecord;
        $ns1->setClass('IN');
        $ns1->setName('@');
        $ns1->setTtl(14400);
        $ns1->setRdata($ns1r);

        $ns2 = new ResourceRecord;
        $ns2->setClass('IN');
        $ns2->setName('@');
        $ns2->setTtl(14400);
        $ns2->setRdata($ns2r);

        $a_rdata = new ARdata;
        $a_rdata->setAddress('192.168.1.0');

        $a_record = new ResourceRecord;
        $a_record->setName('subdomain.au');
        $a_record->setRdata($a_rdata);
        $a_record->setComment("This is a local ip.");

        $aaaa_rdata = new AaaaRdata;
        $aaaa_rdata->setAddress('::1');

        $aaaa_record = new ResourceRecord;
        $aaaa_record->setName('ipv6domain');
        $aaaa_record->setRdata($aaaa_rdata);
        $aaaa_record->setComment("This is an IPv6 domain.");

        $loc_record = new ResourceRecord;
        $loc_record->setName('canberra');
        $loc_record->setRdata(Factory::Loc(
            -35.3075,   //Lat
            149.1244,   //Lon
            500,        //Alt
            20.12,      //Size
            200.3,      //HP
            300.1       //VP
        ));

        $dname = new ResourceRecord;
        $dname->setName('bar.example.com.');
        $dname->setClass(Classes::INTERNET);
        $dname->setRdata(Factory::Dname('foo.example.com.'));

        return new Zone('example.com.', 3600, array(
            $soa,
            $ns1,
            $ns2,
            $a_record,
            $aaaa_record,
            $loc_record,
            $dname,
        ));
    }

    /**
     * Tests a zone file using Bind's Check Zone feature. If CHECKZONE_PATH environment variable has been set.
     */
    public function testZoneFile()
    {
        if (null === $check_zone_path = $this->getEnvVariable(self::PHP_ENV_CHECKZONE_PATH)) {
            $this->markTestSkipped('Bind checkzone path is not defined.');
            return;
        }

        if (!`which $check_zone_path`) {
            $this->markTestSkipped(sprintf('The checkzone path specified "%s" could not be found.', $check_zone_path));
            return;
        }

        $zone = $this->buildTestZone();
        $zoneBuilder = new ZoneBuilder;
        $zoneFile = $zoneBuilder->build($zone);

        $tmpFile = new TempFile('badcow_dns_test_');
        $tmpFile->write($zoneFile);

        if ($this->getEnvVariable(self::PHP_ENV_PRINT_TEST_ZONE)) {
            print PHP_EOL . PHP_EOL;
            print '=====================================TEST ZONE FILE=====================================';
            print PHP_EOL;
            print $zoneFile;
            print PHP_EOL;
            print '=====================================TEST ZONE FILE=====================================';
            print PHP_EOL . PHP_EOL;
        }

        $this->assertTrue(
                Validator::validateZoneFile($zone->getZoneName(), $tmpFile->getPath(), $check_zone_path)
        );
    }
}
