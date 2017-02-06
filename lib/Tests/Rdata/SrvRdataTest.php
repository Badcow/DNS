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

use Badcow\DNS\Rdata\SRV;

class SrvRdataTest extends \PHPUnit_Framework_TestCase
{
    public function testOutput()
    {
        $srv = new SRV;
        $srv->setPort(666);
        $srv->setPriority(10);
        $srv->setWeight(20);
        $srv->setTarget('doom.example.com.');

        $expectation = '10 20 666 doom.example.com.';

        $this->assertEquals($expectation, $srv->output());
    }
}
