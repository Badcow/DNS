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
use Badcow\DNS\Rdata\DNSSEC\DS;

class DsRdataTest extends \PHPUnit\Framework\TestCase
{
    public function testOutput()
    {
        $digest = '2BB183AF5F22588179A53B0A98631FAD1A292118';
        $expectation = '60485 5 1 2BB183AF5F22588179A53B0A98631FAD1A292118';

        $ds = new DS();
        $ds->setKeyTag(60485);
        $ds->setAlgorithm(Algorithms::RSASHA1);
        $ds->setDigestType(Algorithms::RSAMD5);
        $ds->setDigest($digest);

        $this->assertEquals($expectation, $ds->output());
    }
}
