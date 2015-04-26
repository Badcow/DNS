<?php
/*
 * This file is part of Badcow DNS Library.
 *
 * (c) Samuel Williams <sam@badcow.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Badcow\DNS\Test\Rdata;

use Badcow\DNS\Rdata\RdataTrait;

class RdataTraitTest extends \PHPUnit_Framework_TestCase
{
    use RdataTrait;

    const TYPE = 'RDATA_TEST';

    public function output()
    {
        return 'TYPEWRITER';
    }

    public function testGetLength()
    {
        $this->assertEquals(10, $this->getLength());
    }

    public function testGetType()
    {
        $this->assertEquals(self::TYPE, $this->getType());
    }
}
