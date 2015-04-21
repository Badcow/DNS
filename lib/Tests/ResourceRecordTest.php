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

class ResourceRecordTest extends TestCase
{
    /**
     * @expectedException \Badcow\DNS\ResourceRecordException
     */
    public function testSetClass()
    {
        $rr = new ResourceRecord;
        $rr->setClass(Classes::INTERNET);
        $this->assertEquals(Classes::INTERNET, $rr->getClass());
        $rr->setClass('XX');
    }

    /**
     * @expectedException \Badcow\DNS\DNSException
     * @expectedExceptionMessage The name is not a Fully Qualified Domain Name
     */
    public function testSetName()
    {
        $rr = new ResourceRecord;
        $rr->setName('example?record.com.');
    }
} 