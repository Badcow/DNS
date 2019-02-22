<?php

/*
 * This file is part of Badcow DNS Library.
 *
 * (c) Samuel Williams <sam@badcow.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Badcow\DNS\Tests\Rdata;

use Badcow\DNS\Rdata\Factory;
use Badcow\DNS\Rdata\SRV;

class SrvRdataTest extends \PHPUnit\Framework\TestCase
{
    public function testOutput()
    {
        $srv = Factory::Srv(10, 20, 666, 'doom.example.com.');

        $expectation = '10 20 666 doom.example.com.';

        $this->assertEquals($expectation, $srv->output());
        $this->assertEquals(10, $srv->getPriority());
        $this->assertEquals(20, $srv->getWeight());
        $this->assertEquals(666, $srv->getPort());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testPortException()
    {
        $srv = new SRV();
        $srv->setPort(SRV::HIGHEST_PORT + 1);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testPriorityException()
    {
        $srv = new SRV();
        $srv->setPriority(SRV::MAX_PRIORITY + 1);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testWeightException()
    {
        $srv = new SRV();
        $srv->setWeight(SRV::MAX_WEIGHT + 1);
    }
}
