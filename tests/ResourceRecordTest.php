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

use Badcow\DNS\Classes;
use Badcow\DNS\Rdata\Factory;
use Badcow\DNS\ResourceRecord;
use PHPUnit\Framework\TestCase;

class ResourceRecordTest extends TestCase
{
    public function testSetClass(): void
    {
        $rr = new ResourceRecord();
        $rr->setClass(Classes::INTERNET);
        $this->assertEquals(Classes::INTERNET, $rr->getClass());

        $this->expectException(\InvalidArgumentException::class);
        $rr->setClass('XX');
    }

    /**
     * Tests the getter and setter methods.
     */
    public function testSettersAndGetters(): void
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

    public function testUnsetTtl(): void
    {
        $rr = new ResourceRecord();
        $rr->setName('example.com.');
        $ttl = 10800;

        $this->assertNull($rr->getTtl());
        $rr->setTtl($ttl);
        $this->assertEquals($ttl, $rr->getTtl());
        $rr->setTtl(null);
        $this->assertNull($rr->getTtl());
    }
}
