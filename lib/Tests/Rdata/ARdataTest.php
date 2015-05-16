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

use Badcow\DNS\Rdata\ARdata;
use Badcow\DNS\Rdata\RdataException;

class ARdataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ARdata
     */
    private $aRdata;

    public function __construct()
    {
        $this->aRdata = new ARdata;
    }

    public function testGetType()
    {
        $this->assertEquals('A', $this->aRdata->getType());
    }

    public function testSetAddress()
    {
        $address = '192.168.1.1';
        $this->aRdata->setAddress($address);

        $this->assertEquals($address, $this->aRdata->getAddress());
    }

    /**
     * @expectedException \Badcow\DNS\Rdata\RdataException
     */
    public function testException()
    {
        $invalidAddress = '192.168.256.1';
        $this->aRdata->setAddress($invalidAddress);
    }

    public function testOutput()
    {
        $address = '192.168.1.1';
        $this->aRdata->setAddress($address);

        $this->assertEquals($address, $this->aRdata->output());
    }
}
