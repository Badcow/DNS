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

use Badcow\DNS\Rdata\CAA;
use Badcow\DNS\Rdata\Factory;

class CaaRdataTest extends \PHPUnit\Framework\TestCase
{
    public function testOutput()
    {
        $caa = Factory::Caa(0, 'issue', 'letsencrypt.org');

        $expectation = '0 issue "letsencrypt.org"';

        $this->assertEquals($expectation, $caa->output());
        $this->assertEquals(0, $caa->getFlag());
        $this->assertEquals('issue', $caa->getTag());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFlagException()
    {
        $srv = new CAA();
        $srv->setFlag(CAA::MAX_FLAG + 1);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testTagException()
    {
        $srv = new CAA();
        $srv->setTag('not_exist');
    }
}
