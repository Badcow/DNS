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

use Badcow\DNS\Zone;

class ZoneTest extends TestCase
{
    /**
     * @expectedException \Badcow\DNS\ZoneException
     * @expectedExceptionMessage Zone "example.com" is not a fully qualified domain name.
     */
    public function testSetName()
    {
        $zone = new Zone();
        $zone->setName('example.com.');
        $this->assertEquals('example.com.', $zone->getName());

        //Should throw exception
        $zone->setName('example.com');
    }

    public function testGetTtl()
    {
        $ttl = 124567;
        $zone = new Zone();
        $zone->setDefaultTtl($ttl);
        $this->assertEquals($ttl, $zone->getDefaultTtl());
    }

    public function testCtrlEntry()
    {
        $zone = new Zone();
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
