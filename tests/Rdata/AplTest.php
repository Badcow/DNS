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

use Badcow\DNS\Rdata\APL;
use Badcow\DNS\Rdata\Factory;
use PhpIP\IPBlock;
use PhpIP\IPv4Block;
use PhpIP\IPv6Block;
use PHPUnit\Framework\TestCase;

class AplTest extends TestCase
{
    public function testOutput(): void
    {
        $includedRanges = [
            IPBlock::create('192.168.0.0/23'),
            IPBlock::create('2001:acad:1::/112'),
        ];

        $excludedRanges = [
            IPBlock::create('192.168.1.64/28'),
            IPBlock::create('2001:acad:1::8/128'),
        ];

        $apl = Factory::APL($includedRanges, $excludedRanges);

        $expectation = '1:192.168.0.0/23 2:2001:acad:1::/112 !1:192.168.1.64/28 !2:2001:acad:1::8/128';
        $this->assertEquals($expectation, $apl->toText());
    }

    public function testGetters(): void
    {
        $includedRanges = [
            IPBlock::create('192.168.0.0/23'),
            IPBlock::create('2001:acad:1::/112'),
        ];

        $excludedRanges = [
            IPBlock::create('192.168.1.64/28'),
            IPBlock::create('2001:acad:1::8/128'),
        ];

        $apl = Factory::APL($includedRanges, $excludedRanges);

        $this->assertEquals($includedRanges, $apl->getIncludedAddressRanges());
        $this->assertEquals($excludedRanges, $apl->getExcludedAddressRanges());
    }

    /**
     * @throws \Exception
     */
    public function testFromText(): void
    {
        $text = '1:192.168.0.0/23 2:2001:acad:1::/112 !1:192.168.1.64/28 !2:2001:acad:1::8/128';
        $expectation_incl = [
            new IPv4Block('192.168.0.0', 23),
            new IPv6Block('2001:acad:1::', 112),
            ];

        $expectation_excl = [
            new IPv4Block('192.168.1.64', 28),
            new IPv6Block('2001:acad:1::8', 128),
        ];

        /** @var APL $apl */
        $apl = new APL();
        $apl->fromText($text);

        $this->assertCount(2, $apl->getIncludedAddressRanges());
        $this->assertCount(2, $apl->getExcludedAddressRanges());

        $this->assertEquals($expectation_incl, $apl->getIncludedAddressRanges());
        $this->assertEquals($expectation_excl, $apl->getExcludedAddressRanges());
    }

    public function testWire(): void
    {
        $expectation = pack(
            'nCCC4nCC',
            1,                  //Address Family
            24,                 //Prefix
            0 +                 //N: "!" is present
            4,                  //AFD Length
            255,
            255,
            255,
            255,    //AFDPart

            2,                  //Address Family
            64,                 //Prefix
            128 +               //N: "!" is present
            16                  //AFD Length
        ).inet_pton('2001:acad:dead:beef::'); //AFDPart

        $apl = new APL();
        $apl->addAddressRange(IPBlock::create('255.255.255.255/24'), true);
        $apl->addAddressRange(IPBlock::create('2001:acad:dead:beef::/64'), false);

        $this->assertEquals($expectation, $apl->toWire());
        $aplFromWire = new APL();
        $aplFromWire->fromWire($expectation);

        $this->assertCount(1, $apl->getIncludedAddressRanges());
        $this->assertCount(1, $apl->getExcludedAddressRanges());
        $this->assertEquals($apl, $aplFromWire);
    }
}
