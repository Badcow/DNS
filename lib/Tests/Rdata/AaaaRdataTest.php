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

class AaaaRdataTest extends \PHPUnit_Framework_TestCase
{
    public function testSetAddress()
    {
        $address = '2003:dead:beef:4dad:23:46:bb:101';
        $aaaa = new AaaaRdata;
        $aaaa->setAddress($address);

        $this->assertEquals($address, $aaaa->getAddress());
    }

    /**
     * @expectedException \Badcow\DNS\Rdata\RdataException
     */
    public function testException()
    {
        $address = '2001::0234:C1ab::A0:aabc:003F';
        $aaaa = new AaaaRdata;
        $aaaa->setAddress($address);
    }
}
