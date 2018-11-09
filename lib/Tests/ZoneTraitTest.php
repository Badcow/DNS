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

class ZoneTraitTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return \Badcow\DNS\ZoneInterface
     *
     * @throws \ReflectionException
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
}
