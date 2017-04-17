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

use Badcow\DNS\Classes;
use Badcow\DNS\ResourceRecord;
use Badcow\DNS\Zone;
use Badcow\DNS\Rdata\Factory;

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

    public function testExpand()
    {
        $rrs = [
            new ResourceRecord('@', Factory::A('1.1.1.1')),
            new ResourceRecord('@', Factory::Aaaa('2001:db8::f00')),
            new ResourceRecord('mail1', Factory::Mx(10, 'mail.example.com.'), 1800, Classes::HESIOD),
            new ResourceRecord('@', Factory::Mx(20, 'mail')),
            new ResourceRecord('www', Factory::Cname('@')),
            new ResourceRecord('ftp', Factory::Cname('www')),
            new ResourceRecord('dns1', Factory::Ns('nameserver-001')),
        ];

        $zone = new Zone('example.com.', 3600, $rrs);

        $zone->expand();

        /**
         * @var ResourceRecord $a
         * @var ResourceRecord $a4
         * @var ResourceRecord $mx1
         * @var ResourceRecord $mx2
         * @var ResourceRecord $cname_1
         * @var ResourceRecord $cname_2
         * @var ResourceRecord $ns
         */
        list($a, $a4, $mx1, $mx2, $cname_1, $cname_2, $ns) = $zone->getResourceRecords();

        $this->assertEquals('example.com.', $a->getName());
        $this->assertEquals(3600, $a->getTtl());
        $this->assertEquals(Classes::HESIOD, $a->getClass());

        $this->assertEquals('2001:0db8:0000:0000:0000:0000:0000:0f00', $a4->getRdata()->getAddress());

        $this->assertEquals('mail1.example.com.', $mx1->getName());
        $this->assertEquals(1800, $mx1->getTtl());
        $this->assertEquals(Classes::HESIOD, $mx1->getClass());

        $this->assertEquals('mail.example.com.', $mx2->getRdata()->getExchange());

        $this->assertEquals('www.example.com.', $cname_1->getName());
        $this->assertEquals('example.com.', $cname_1->getRdata()->getTarget());

        $this->assertEquals('www.example.com.', $cname_2->getRdata()->getTarget());

        $this->assertEquals('nameserver-001.example.com.', $ns->getRdata()->getTarget());
    }
}
