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

use Badcow\DNS\Rdata\Factory;
use Badcow\DNS\Rdata\SOA;

class SoaRdataTest extends \PHPUnit\Framework\TestCase
{
    public function testSettersAndGetters(): void
    {
        $soa = new SOA();
        $mname = 'example.com.';
        $rname = 'post.example.com.';
        $serial = 1970010101;
        $refresh = 3600;
        $retry = 14400;
        $expire = 604800;
        $minimum = 3600;

        $soa->setMname($mname);
        $soa->setRname($rname);
        $soa->setSerial($serial);
        $soa->setRefresh($refresh);
        $soa->setRetry($retry);
        $soa->setExpire($expire);
        $soa->setMinimum($minimum);

        $this->assertEquals($mname, $soa->getMname());
        $this->assertEquals($rname, $soa->getRname());
        $this->assertEquals($serial, $soa->getSerial());
        $this->assertEquals($refresh, $soa->getRefresh());
        $this->assertEquals($retry, $soa->getRetry());
        $this->assertEquals($expire, $soa->getExpire());
        $this->assertEquals($minimum, $soa->getMinimum());
    }

    public function testOutput(): void
    {
        $soa = Factory::Soa(
            'example.com.',
            'postmaster.example.com.',
            2015042101,
            3600,
            14400,
            604800,
            3600
        );

        $expected = 'example.com. postmaster.example.com. 2015042101 3600 14400 604800 3600';

        $this->assertEquals($expected, $soa->output());
    }
}
