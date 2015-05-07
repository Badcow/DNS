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
    public function testSetZoneName()
    {
        $zone = new Zone;
        $zone->setName('example.com.');
        $this->assertEquals('example.com.', $zone->getName());
        $zone->setName('example.com');
    }

    public function testGetTtl()
    {
        $ttl = 124567;
        $zone = new Zone;
        $zone->setDefaultTtl($ttl);
        $this->assertEquals($ttl, $zone->getDefaultTtl());
    }
}
