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

use Badcow\DNS\Rdata\ARdata;
use Badcow\DNS\Rdata\DNSSEC\NsecRdata;
use Badcow\DNS\Rdata\DNSSEC\RrsigRdata;
use Badcow\DNS\Rdata\MxRdata;

class NsecRdataTest extends \PHPUnit_Framework_TestCase
{
    public function testOutput()
    {
        $expectation = 'host.example.com. A MX RRSIG NSEC';

        $nsec = new NsecRdata();
        $nsec->setNextDomainName('host.example.com.');
        $nsec->addTypeBitMap(ARdata::TYPE);
        $nsec->addTypeBitMap(MxRdata::TYPE);
        $nsec->addTypeBitMap(RrsigRdata::TYPE);
        $nsec->addTypeBitMap(NsecRdata::TYPE);

        $this->assertEquals($expectation, $nsec->output());
    }
}
