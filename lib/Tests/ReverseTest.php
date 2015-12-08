<?php

/*
 * This file is part of Badcow DNS Library.
 *
 * (c) Samuel Williams <sam@badcow.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
namespace Badcow\DNS\Tests;

use Badcow\DNS\AlignedBuilder;
use Badcow\DNS\Zone;
use Badcow\DNS\Ip\Toolbox;
use Badcow\DNS\Rdata\Factory;
use Badcow\DNS\ResourceRecord;
use Badcow\DNS\Classes;

class ReverseTest extends TestCase
{
    public function testReverseRecord()
    {
        $origin = Toolbox::reverseIpv4('192.168.8');

        $soa = new ResourceRecord('@', Factory::Soa(
            'example.com.',
            'post.example.com.',
            2015010101,
            3600,
            14400,
            604800,
            3600
        ));

        $ns1 = new ResourceRecord($origin, Factory::Ns('ns1.example.com.'), null, Classes::INTERNET);
        $ns2 = new ResourceRecord($origin, Factory::Ns('ns2.example.com.'), null, Classes::INTERNET);

        $foo1 = new ResourceRecord('1', Factory::Ptr('foo1.example.com.'), null, Classes::INTERNET);
        $foo2 = new ResourceRecord('2', Factory::Ptr('foo2.example.com.'), null, Classes::INTERNET);
        $foo3 = new ResourceRecord('3', Factory::Ptr('foo3.example.com.'), null, Classes::INTERNET);
        $foo4 = new ResourceRecord('4', Factory::Ptr('foo4.example.com.'), null, Classes::INTERNET);
        $foo5 = new ResourceRecord('5', Factory::Ptr('foo5.example.com.'), null, Classes::INTERNET);

        $zone = new Zone($origin, 14400, array(
            $soa,
            $ns1,
            $ns2,
            $foo1,
            $foo2,
            $foo3,
            $foo4,
            $foo5,
        ));

        $this->bindTest($zone, new AlignedBuilder);
    }
}