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

class ZoneTraitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return \Badcow\DNS\ZoneInterface
     */
    private function getZone()
    {
        return $this->getObjectForTrait('\Badcow\DNS\ZoneTrait');
    }

    public function testSetName()
    {
        $name = 'example.com.';
        $zone = $this->getZone();
        $zone->setName($name);

        $this->assertEquals($name, $zone->getName());
    }

    public function testGetTtl()
    {
        $ttl = 124567;
        $zone = $this->getZone();
        $zone->setDefaultTtl($ttl);
        $this->assertEquals($ttl, $zone->getDefaultTtl());
    }

    public function testCtrlEntry()
    {
        $zone = $this->getZone();
        $zone->addControlEntry('test1', 1234);
        $zone->addControlEntry('test1', 4321);
        $zone->addControlEntry('test2', 5678);
        $zone->addControlEntry('test3', 9865);

        $this->assertEquals([1234, 4321], $zone->getControlEntry('test1'));
        $this->assertEquals([
            ['name' => 'test1', 'value' => 1234],
            ['name' => 'test1', 'value' => 4321],
            ['name' => 'test2', 'value' => 5678],
            ['name' => 'test3', 'value' => 9865],
        ], $zone->getControlEntries());
    }
}
