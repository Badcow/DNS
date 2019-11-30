<?php

declare(strict_types=1);

/*
 * This file is part of Badcow DNS Library.
 *
 * (c) Samuel Williams <sam@badcow.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Badcow\DNS\Tests;

use Badcow\DNS\Classes;
use Badcow\DNS\Rdata\Factory;
use Badcow\DNS\Rdata\PTR;
use Badcow\DNS\ResourceRecord;
use Badcow\DNS\Zone;
use Badcow\DNS\ZoneBuilder;
use PHPUnit\Framework\TestCase;

class ReverseTest extends TestCase
{
    /**
     * @var string
     */
    private $expectedIpv4Record = <<< 'TXT'
$ORIGIN 8.168.192.in-addr.arpa.
$TTL 14400
@ IN SOA example.com. post.example.com. 2015010101 3600 14400 604800 3600
@ IN NS ns1.example.com.
@ IN NS ns2.example.com.
1 IN PTR foo1.example.com.
2 IN PTR foo2.example.com.
3 IN PTR foo3.example.com.
4 IN PTR foo4.example.com.
5 IN PTR foo5.example.com.

TXT;

    /**
     * @var string
     */
    private $expectedIpv6Record = <<< 'TXT'
$ORIGIN 1.2.0.0.3.8.f.0.1.0.0.2.ip6.arpa.
$TTL 14400
@ IN SOA example.com. post.example.com. 2015010101 3600 14400 604800 3600
@ IN NS ns1.example.com.
@ IN NS ns2.example.com.
8 IN PTR foo8.example.com.
9 IN PTR foo9.example.com.
a IN PTR fooa.example.com.
b IN PTR foob.example.com.
c IN PTR fooc.example.com.

TXT;

    protected function setUp(): void
    {
        $this->expectedIpv4Record = str_replace("\r", '', $this->expectedIpv4Record);
        $this->expectedIpv6Record = str_replace("\r", '', $this->expectedIpv6Record);
    }

    public function testReverseIpv4Record(): void
    {
        $origin = PTR::reverseIpv4('192.168.8');

        $soa = ResourceRecord::create('@', Factory::SOA(
            'example.com.',
            'post.example.com.',
            2015010101,
            3600,
            14400,
            604800,
            3600
        ), null, Classes::INTERNET);

        $ns1 = ResourceRecord::create('@', Factory::NS('ns1.example.com.'), null, Classes::INTERNET);
        $ns2 = ResourceRecord::create('@', Factory::NS('ns2.example.com.'), null, Classes::INTERNET);

        $foo1 = ResourceRecord::create('1', Factory::PTR('foo1.example.com.'), null, Classes::INTERNET);
        $foo2 = ResourceRecord::create('2', Factory::PTR('foo2.example.com.'), null, Classes::INTERNET);
        $foo3 = ResourceRecord::create('3', Factory::PTR('foo3.example.com.'), null, Classes::INTERNET);
        $foo4 = ResourceRecord::create('4', Factory::PTR('foo4.example.com.'), null, Classes::INTERNET);
        $foo5 = ResourceRecord::create('5', Factory::PTR('foo5.example.com.'), null, Classes::INTERNET);

        $zone = new Zone($origin, 14400, [
            $soa,
            $ns1,
            $ns2,
            $foo1,
            $foo2,
            $foo3,
            $foo4,
            $foo5,
        ]);

        $builder = new ZoneBuilder();

        $this->assertEquals($this->expectedIpv4Record, $builder->build($zone));
    }

    public function testReverseIpv6Record(): void
    {
        $origin = PTR::reverseIpv6('2001:f83:21');

        $soa = ResourceRecord::create('@', Factory::SOA(
            'example.com.',
            'post.example.com.',
            2015010101,
            3600,
            14400,
            604800,
            3600
        ), null, Classes::INTERNET);

        $ns1 = ResourceRecord::create('@', Factory::NS('ns1.example.com.'), null, Classes::INTERNET);
        $ns2 = ResourceRecord::create('@', Factory::NS('ns2.example.com.'), null, Classes::INTERNET);

        $foo8 = ResourceRecord::create('8', Factory::PTR('foo8.example.com.'), null, Classes::INTERNET);
        $foo9 = ResourceRecord::create('9', Factory::PTR('foo9.example.com.'), null, Classes::INTERNET);
        $fooa = ResourceRecord::create('a', Factory::PTR('fooa.example.com.'), null, Classes::INTERNET);
        $foob = ResourceRecord::create('b', Factory::PTR('foob.example.com.'), null, Classes::INTERNET);
        $fooc = ResourceRecord::create('c', Factory::PTR('fooc.example.com.'), null, Classes::INTERNET);

        $zone = new Zone($origin, 14400, [
            $soa,
            $ns1,
            $ns2,
            $foo8,
            $foo9,
            $fooa,
            $foob,
            $fooc,
        ]);

        $builder = new ZoneBuilder();

        $this->assertEquals($this->expectedIpv6Record, $builder->build($zone));
    }
}
