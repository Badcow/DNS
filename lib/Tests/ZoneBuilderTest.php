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

use Badcow\DNS\ZoneBuilder;

class ZoneBuilderTest extends TestCase
{
    public function testBuild()
    {
        $zone = $this->buildTestZone();
        $zoneBuilder = new ZoneBuilder();
        $this->assertEquals($this->expected, $output = $zoneBuilder->build($zone));

        if (true == $this->getEnvVariable(self::PHP_ENV_PRINT_TEST_ZONE)) {
            $this->printBlock($output, 'TEST ZONE FILE');
        }
    }

    /**
     * Test the Zone using Bind.
     */
    public function testZoneFile()
    {
        $this->bindTest($this->buildTestZone(), new ZoneBuilder());
    }
}
