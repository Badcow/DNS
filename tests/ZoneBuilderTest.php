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

use Badcow\DNS\ZoneBuilder;
use PHPUnit\Framework\TestCase;

class ZoneBuilderTest extends TestCase
{
    public function testBuild(): void
    {
        $zone = TestZone::buildTestZone();
        $zoneBuilder = new ZoneBuilder();
        $this->assertEquals(TestZone::getExpectation(), $output = $zoneBuilder->build($zone));
    }
}
