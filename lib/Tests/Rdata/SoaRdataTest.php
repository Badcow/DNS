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

use Badcow\DNS\Rdata\Factory;
use Badcow\DNS\Rdata\SoaRdata;

class SoaRdataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Badcow\DNS\Rdata\RdataException
     */
    public function testSetMnameException()
    {
        $target = 'foo.example.com';
        $soa = new SoaRdata();
        $soa->setMname($target);
    }

    /**
     * @expectedException \Badcow\DNS\Rdata\RdataException
     */
    public function testSetRnameException()
    {
        $target = 'foo.example.com';
        $soa = new SoaRdata();
        $soa->setRname($target);
    }

    public function testOutput()
    {
        $soa = Factory::Soa(
            'example.com.',
            'postmaster.example.com.',
            '2015042101',
            3600,
            14400,
            604800,
            3600,
            false
        );

        $expected = 'example.com. postmaster.example.com. 2015042101 3600 14400 604800 3600';

        $this->assertEquals($expected, $soa->output());
    }
}
