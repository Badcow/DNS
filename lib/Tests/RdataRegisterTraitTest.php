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

use Badcow\DNS\RdataRegisterTrait;
use Badcow\DNS\Tests\Rdata\DummyRdata;


class RdataRegisterTraitTest extends \PHPUnit\Framework\TestCase
{
    use RdataRegisterTrait;

    public function testRegisterRdataType()
    {
        $this->registerRdataType(DummyRdata::TYPE, '\\Badcow\\DNS\\Tests\\Rdata\\DummyRdata');
        $this->assertTrue($this->hasRdataType(DummyRdata::TYPE));
        $this->assertArrayHasKey(DummyRdata::TYPE, $this->rdataTypes);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testRegisterRdataTypeException()
    {
        $this->registerRdataType(DummyRdata::TYPE, '\\Badcow\\DNS\\Tests\\ClassesTest');
    }

    public function testRemoveRdataType()
    {
        $this->registerRdataType(DummyRdata::TYPE, '\\Badcow\\DNS\\Tests\\Rdata\\DummyRdata');
        $this->assertTrue($this->hasRdataType(DummyRdata::TYPE));
        $this->removeRdataType(DummyRdata::TYPE);
        $this->assertFalse($this->hasRdataType(DummyRdata::TYPE));

        $this->removeRdataType(DummyRdata::TYPE);
        $this->assertFalse($this->hasRdataType(DummyRdata::TYPE));
    }

    public function testGetRegisteredTypes()
    {
        $this->registerRdataType(DummyRdata::TYPE, '\\Badcow\\DNS\\Tests\\Rdata\\DummyRdata');
        $this->assertContains(DummyRdata::TYPE, $this->getRegisteredTypes());
    }

    /**
     * @expectedException \DomainException
     */
    public function testGetNewRdataByType()
    {
        $this->registerRdataType(DummyRdata::TYPE, '\\Badcow\\DNS\\Tests\\Rdata\\DummyRdata');
        $dummy = $this->getNewRdataByType(DummyRdata::TYPE);
        $this->assertInstanceOf('\\Badcow\\DNS\\Tests\\Rdata\\DummyRdata', $dummy);
        $this->getNewRdataByType('DOESNOTEXIST');
    }
}
