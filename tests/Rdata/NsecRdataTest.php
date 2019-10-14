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

use Badcow\DNS\Rdata\A;
use Badcow\DNS\Rdata\Factory;
use Badcow\DNS\Rdata\MX;
use Badcow\DNS\Rdata\NS;
use Badcow\DNS\Rdata\NSEC;
use Badcow\DNS\Rdata\PTR;
use Badcow\DNS\Rdata\RRSIG;
use PHPUnit\Framework\TestCase;

class NsecRdataTest extends TestCase
{
    public function testOutput(): void
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

    public function testFactory(): void
    {
        $nextDomain = 'host.example.com.';
        $bitMaps = [A::TYPE, MX::TYPE, RRSIG::TYPE, NSEC::TYPE];
        $nsec = Factory::Nsec($nextDomain, $bitMaps);

        $this->assertEquals($nextDomain, $nsec->getNextDomainName());
        $this->assertEquals($bitMaps, $nsec->getTypeBitMaps());
    }

    public function testClearTypeMap(): void
    {
        $nsec = new NSEC();
        $nsec->addTypeBitMap(NS::TYPE);
        $nsec->addTypeBitMap(PTR::TYPE);

        $this->assertEquals([NS::TYPE, PTR::TYPE], $nsec->getTypeBitMaps());
        $nsec->clearTypeMap();
        $this->assertEquals([], $nsec->getTypeBitMaps());
    }
}
