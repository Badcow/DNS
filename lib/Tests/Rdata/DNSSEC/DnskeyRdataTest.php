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
use Badcow\DNS\Rdata\DNSSEC\DNSKEY;

class DnskeyRdataTest extends \PHPUnit_Framework_TestCase
{
    public function testOutput()
    {
        $publicKey = 'AQPSKmynfzW4kyBv015MUG2DeIQ3Cbl+BBZH4b/0PY1kxkmvHjcZc8nokfzj31GajIQKY+5CptLr3buXA10hWqTkF7H6RfoRqXQeogmMHfpftf6zMv1LyBUgia7za6ZEzOJBOztyvhjL742iU/TpPSEDhm2SNKLijfUppn1UaNvv4w==';
        $expectation = '256 3 5 AQPSKmynfzW4kyBv015MUG2DeIQ3Cbl+BBZH4b/0PY1kxkmvHjcZc8nokfzj31GajIQKY+5CptLr3buXA10hWqTkF7H6RfoRqXQeogmMHfpftf6zMv1LyBUgia7za6ZEzOJBOztyvhjL742iU/TpPSEDhm2SNKLijfUppn1UaNvv4w==';

        $dnskey = new DNSKEY();
        $dnskey->setFlags(256);
        $dnskey->setAlgorithm(Algorithms::RSASHA1);
        $dnskey->setPublicKey($publicKey);

        $this->assertEquals($expectation, $dnskey->output());
    }
}
