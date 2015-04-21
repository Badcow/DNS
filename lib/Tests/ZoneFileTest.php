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
    Badcow\DNS\Validator,
    Badcow\DNS\ZoneBuilder,
    Badcow\DNS\Classes,
    Badcow\DNS\Rdata\Factory;

class ZoneFileTest extends TestCase
{
    const PHP_ENV_CHECKZONE_PATH = 'CHECKZONE_PATH';
    const PHP_ENV_PRINT_TEST_ZONE = 'PRINT_TEST_ZONE';

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
