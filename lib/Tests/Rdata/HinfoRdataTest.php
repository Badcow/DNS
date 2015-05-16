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

use Badcow\DNS\Rdata\AaaaRdata;
use Badcow\DNS\Rdata\HinfoRdata;
use Badcow\DNS\Rdata\TxtRdata;

class HinfoRdataTest extends \PHPUnit_Framework_TestCase
{
    public function testOutput()
    {
        $cpu = '2.7GHz';
        $os = 'Ubuntu 12.04';
        $expectation = '"2.7GHz" "Ubuntu 12.04"';
        $hinfo = new HinfoRdata;
        $hinfo->setCpu($cpu);
        $hinfo->setOs($os);

        $this->assertEquals($expectation, $hinfo->output());
    }

    public function testGetters()
    {
        $cpu = '2.7GHz';
        $os = 'Ubuntu 12.04';
        $hinfo = new HinfoRdata;
        $hinfo->setCpu($cpu);
        $hinfo->setOs($os);

        $this->assertEquals($cpu, $hinfo->getCpu());
        $this->assertEquals($os, $hinfo->getOs());
    }
}
