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
use Badcow\DNS\Rdata\Factory;
use Badcow\DNS\ResourceRecord;

class ResourceRecordTest extends TestCase
{
    /**
     * @expectedException \Badcow\DNS\ResourceRecordException
     */
    public function testSetClass()
    {
        $rr = new ResourceRecord();
        $rr->setClass(Classes::INTERNET);
        $this->assertEquals(Classes::INTERNET, $rr->getClass());
        $rr->setClass('XX');
    }

    /**
     * @expectedException \Badcow\DNS\DNSException
     * @expectedExceptionMessage "example?record.com." is not a valid resource record name.
     */
    public function testSetName()
    {
        $rr = new ResourceRecord();
        $rr->setName('example?record.com.');
    }

    public function testSettersAndGetters()
    {
        $rr = new ResourceRecord();
        $name = 'test';
        $ttl = 3500;
        $comment = 'Hello';
        $a = Factory::A('192.168.7.7');

        $rr->setName($name);
        $rr->setClass(Classes::INTERNET);
        $rr->setRdata($a);
        $rr->setTtl($ttl);
        $rr->setComment($comment);

        $this->assertEquals($a, $rr->getRdata());
        $this->assertEquals($name, $rr->getName());
        $this->assertEquals($ttl, $rr->getTtl());
        $this->assertEquals($comment, $rr->getComment());
        $this->assertEquals($a->getType(), $rr->getType());
    }
}
