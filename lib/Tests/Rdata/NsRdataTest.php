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

use Badcow\DNS\Rdata\NsRdata;

class NsRdataTest extends \PHPUnit_Framework_TestCase
{
    public function testSetNsdname()
    {
        $nsdname = 'foo.example.com.';
        $dname = new NsRdata();
        $dname->setTarget($nsdname);

        $this->assertEquals($nsdname, $dname->getTarget());
    }

    public function testOutput()
    {
        $Nsdname = 'foo.example.com.';
        $dname = new NsRdata();
        $dname->setTarget($Nsdname);

        $this->assertEquals($Nsdname, $dname->output());
    }
}
