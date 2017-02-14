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


use Badcow\DNS\Rdata\DNSSEC\Algorithms;
use Badcow\DNS\Rdata\DNSSEC\RRSIG;

class RrsigRdataTest extends \PHPUnit_Framework_TestCase
{
    public function testOutput()
    {
        $signature = 'oJB1W6WNGv+ldvQ3WDG0MQkg5IEhjRip8WTrPYGv07h108dUKGMeDPKijVCHX3DDKdfb+v6oB9wfuh3DTJXUAfI/M0zmO/z' .
            'z8bW0Rznl8O3tGNazPwQKkRN20XPXV6nwwfoXmJQbsLNrLfkGJ5D6fwFm8nN+6pBzeDQfsS3Ap3o=';

        $expectation = 'A 5 3 86400 20050322173103 20030220173103 2642 example.com. ' . $signature;

        $rrsig = new RRSIG();

        $rrsig->setTypeCovered('A');
        $rrsig->setAlgorithm(Algorithms::RSASHA1);
        $rrsig->setLabels(3);
        $rrsig->setOriginalTtl(86400);
        $rrsig->setSignatureExpiration(20050322173103);
        $rrsig->setSignatureInception(20030220173103);
        $rrsig->setKeyTag(2642);
        $rrsig->setSignersName('example.com.');
        $rrsig->setSignature($signature);

        $this->assertEquals($expectation, $rrsig->output());
    }
}
