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

use Badcow\DNS\Rdata\CNAME;


class CnameRdataTest extends \PHPUnit\Framework\TestCase
{
    public function testOutput()
    {
        $target = 'foo.example.com.';
        $cname = new CNAME();
        $cname->setTarget($target);

        $this->assertEquals($target, $cname->output());
    }
}
