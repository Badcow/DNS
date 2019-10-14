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

use Badcow\DNS\Rdata\NS;

class NsRdataTest extends \PHPUnit\Framework\TestCase
{
    public function testSetNsdname(): void
    {
        $nsdname = 'foo.example.com.';
        $dname = new NS();
        $dname->setTarget($nsdname);

        $this->assertEquals($nsdname, $dname->getTarget());
    }

    public function testOutput(): void
    {
        $Nsdname = 'foo.example.com.';
        $dname = new NS();
        $dname->setTarget($Nsdname);

        $this->assertEquals($Nsdname, $dname->output());
    }
}
