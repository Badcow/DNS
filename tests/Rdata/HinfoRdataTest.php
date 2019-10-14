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

use Badcow\DNS\Rdata\HINFO;

class HinfoRdataTest extends \PHPUnit\Framework\TestCase
{
    public function testOutput(): void
    {
        $cpu = '2.7GHz';
        $os = 'Ubuntu 12.04';
        $expectation = '"2.7GHz" "Ubuntu 12.04"';
        $hinfo = new HINFO();
        $hinfo->setCpu($cpu);
        $hinfo->setOs($os);

        $this->assertEquals($expectation, $hinfo->output());
    }

    public function testGetters(): void
    {
        $cpu = '2.7GHz';
        $os = 'Ubuntu 12.04';
        $hinfo = new HINFO();
        $hinfo->setCpu($cpu);
        $hinfo->setOs($os);

        $this->assertEquals($cpu, $hinfo->getCpu());
        $this->assertEquals($os, $hinfo->getOs());
    }
}
