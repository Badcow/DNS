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

namespace Badcow\DNS\Tests\Rdata;

use Badcow\DNS\Rdata\PTR;
use PHPUnit\Framework\TestCase;

class PtrTest extends TestCase
{
    public function provider_expandIPv6(): array
    {
        return [
            ['0000:0000:0000:0000:0000:0000:0000:0001', '::1'],
            ['2001:0db8:0000:0000:0000:ff00:0042:8329', '2001:db8::ff00:42:8329'],
            ['2001:0000:0000:acad:0000:0000:0000:0001', '2001:0:0:acad::1'],
            ['0000:0000:0000:0000:0000:0000:0000:0000', '::'],
            ['2001:0000:0000:ab80:2390:0000:0000:000a', '2001::ab80:2390:0:0:a'],
            ['0000:0000:aaaa:0000:0000:aaaa:0000:0000', '::aaaa:0:0:aaaa:0:0'],
            ['0001:0000:0000:0000:0000:0000:0000:0000', '1::'],
        ];
    }

    public function provider_contractIPv6(): array
    {
        return array_merge($this->provider_expandIPv6(), [
            ['2001:db8:0:0:f:0:0:0', '2001:db8:0:0:f::'],
            ['2001:db8::ff00:42:8329', '2001:db8::ff00:42:8329'],
            ['2001:db8:a:bac:8099:d:f:9', '2001:db8:a:bac:8099:d:f:9'],
        ]);
    }

    /**
     * @dataProvider provider_expandIPv6
     */
    public function testExpandIpv6(string $expectation, string $ip): void
    {
        $this->assertEquals($expectation, PTR::expandIpv6($ip));
    }

    /**
     * @dataProvider provider_contractIPv6
     */
    public function testContractIpv6(string $ip, string $expectation): void
    {
        $this->assertEquals($expectation, PTR::contractIpv6($ip));
    }

    public function testContractIpv6ThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('"127.0.0.1" is not a valid IPv6 address.');

        PTR::contractIpv6('127.0.0.1');
    }

    public function testReverseIpv4(): void
    {
        $case_1 = '192.168.1.213';
        $exp_1 = '213.1.168.192.in-addr.arpa.';

        $this->assertEquals($exp_1, PTR::reverseIpv4($case_1));
    }

    public function testReverseIpv6(): void
    {
        $case_1 = '2001:db8::567:89ab';
        $case_2 = '8007:ea:19';

        $exp_1 = 'b.a.9.8.7.6.5.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.8.b.d.0.1.0.0.2.ip6.arpa.';
        $exp_2 = '9.1.0.0.a.e.0.0.7.0.0.8.ip6.arpa.';

        $this->assertEquals($exp_1, PTR::reverseIpv6($case_1));
        $this->assertEquals($exp_2, PTR::reverseIpv6($case_2));
    }
}
