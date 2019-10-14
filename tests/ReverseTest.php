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
use Badcow\DNS\Ip\Toolbox;
use Badcow\DNS\Rdata\Factory;
use Badcow\DNS\ResourceRecord;
use Badcow\DNS\Zone;
use Badcow\DNS\ZoneBuilder;

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
        $this->normaliseLineFeeds($this->expectedIpv4Record);
        $this->normaliseLineFeeds($this->expectedIpv6Record);
    }

    public function testReverseIpv4Record(): void
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
        ), null, Classes::INTERNET);

        $ns1 = new ResourceRecord('@', Factory::Ns('ns1.example.com.'), null, Classes::INTERNET);
        $ns2 = new ResourceRecord('@', Factory::Ns('ns2.example.com.'), null, Classes::INTERNET);

        $foo1 = new ResourceRecord('1', Factory::Ptr('foo1.example.com.'), null, Classes::INTERNET);
        $foo2 = new ResourceRecord('2', Factory::Ptr('foo2.example.com.'), null, Classes::INTERNET);
        $foo3 = new ResourceRecord('3', Factory::Ptr('foo3.example.com.'), null, Classes::INTERNET);
        $foo4 = new ResourceRecord('4', Factory::Ptr('foo4.example.com.'), null, Classes::INTERNET);
        $foo5 = new ResourceRecord('5', Factory::Ptr('foo5.example.com.'), null, Classes::INTERNET);

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
        $origin = Toolbox::reverseIpv6('2001:f83:21');

        $soa = new ResourceRecord('@', Factory::Soa(
            'example.com.',
            'post.example.com.',
            2015010101,
            3600,
            14400,
            604800,
            3600
        ), null, Classes::INTERNET);

        $ns1 = new ResourceRecord('@', Factory::Ns('ns1.example.com.'), null, Classes::INTERNET);
        $ns2 = new ResourceRecord('@', Factory::Ns('ns2.example.com.'), null, Classes::INTERNET);

        $foo8 = new ResourceRecord('8', Factory::Ptr('foo8.example.com.'), null, Classes::INTERNET);
        $foo9 = new ResourceRecord('9', Factory::Ptr('foo9.example.com.'), null, Classes::INTERNET);
        $fooa = new ResourceRecord('a', Factory::Ptr('fooa.example.com.'), null, Classes::INTERNET);
        $foob = new ResourceRecord('b', Factory::Ptr('foob.example.com.'), null, Classes::INTERNET);
        $fooc = new ResourceRecord('c', Factory::Ptr('fooc.example.com.'), null, Classes::INTERNET);

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
