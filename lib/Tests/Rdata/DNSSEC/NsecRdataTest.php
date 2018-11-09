<?php

/*
 * This file is part of Badcow-DNS.
 *
 * (c) Samuel Williams <sam@badcow.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Badcow\DNS\Tests\Rdata\DNSSEC;

use Badcow\DNS\Rdata\A;
use Badcow\DNS\Rdata\DNSSEC\NSEC;
use Badcow\DNS\Rdata\DNSSEC\RRSIG;
use Badcow\DNS\Rdata\MX;

class NsecRdataTest extends \PHPUnit\Framework\TestCase
{
    public function testOutput()
    {
        $expectation = 'host.example.com. A MX RRSIG NSEC';

        $nsec = new NSEC();
        $nsec->setNextDomainName('host.example.com.');
        $nsec->addTypeBitMap(A::TYPE);
        $nsec->addTypeBitMap(MX::TYPE);
        $nsec->addTypeBitMap(RRSIG::TYPE);
        $nsec->addTypeBitMap(NSEC::TYPE);

        $this->assertEquals($expectation, $nsec->output());
    }
}
