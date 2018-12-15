<?php

namespace Badcow\DNS\Tests\Rdata;

use Badcow\DNS\Rdata\APL;
use PHPUnit\Framework\TestCase;

class AplTest extends TestCase
{
    public function testOutput()
    {
        $apl = new APL();
        $apl->addAddressRange(\IPBlock::create('192.168.0.0/23'));
        $apl->addAddressRange(\IPBlock::create('192.168.1.64/28'), false);
        $apl->addAddressRange(\IPBlock::create('2001:acad:1::/112'), true);
        $apl->addAddressRange(\IPBlock::create('2001:acad:1::8/128'), false);

        $expectation = '1:192.168.0.0/23 2:2001:acad:1::/112 !1:192.168.1.64/28 !2:2001:acad:1::8/128';
        $this->assertEquals($expectation, $apl->output());
    }
}
