<?php

namespace Badcow\DNS\Tests\Rdata;

use Badcow\DNS\Rdata\APL;
use Badcow\DNS\Rdata\Factory;
use PHPUnit\Framework\TestCase;

class AplTest extends TestCase
{
    public function testOutput()
    {
        $includedRanges = [
            \IPBlock::create('192.168.0.0/23'),
            \IPBlock::create('2001:acad:1::/112'),
        ];

        $excludedRanges = [
            \IPBlock::create('192.168.1.64/28'),
            \IPBlock::create('2001:acad:1::8/128'),
        ];

        $apl = Factory::Apl($includedRanges, $excludedRanges);

        $expectation = '1:192.168.0.0/23 2:2001:acad:1::/112 !1:192.168.1.64/28 !2:2001:acad:1::8/128';
        $this->assertEquals($expectation, $apl->output());
    }

    public function testGetters()
    {
        $includedRanges = [
            \IPBlock::create('192.168.0.0/23'),
            \IPBlock::create('2001:acad:1::/112'),
        ];

        $excludedRanges = [
            \IPBlock::create('192.168.1.64/28'),
            \IPBlock::create('2001:acad:1::8/128'),
        ];

        $apl = Factory::Apl($includedRanges, $excludedRanges);

        $this->assertEquals($includedRanges, $apl->getIncludedAddressRanges());
        $this->assertEquals($excludedRanges, $apl->getExcludedAddressRanges());
    }
}
