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

use Badcow\DNS\Rdata\DNAME;


class DnameRdataTest extends \PHPUnit\Framework\TestCase
{
    public function testSetTarget()
    {
        $target = 'foo.example.com.';
        $dname = new DNAME();
        $dname->setTarget($target);

        $this->assertEquals($target, $dname->getTarget());
    }

    public function testOutput()
    {
        $target = 'foo.example.com.';
        $dname = new DNAME();
        $dname->setTarget($target);

        $this->assertEquals($target, $dname->output());
    }
}
