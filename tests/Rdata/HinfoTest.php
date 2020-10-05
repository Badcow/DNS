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
use Badcow\DNS\Rdata\HINFO;
use PHPUnit\Framework\TestCase;

class HinfoTest extends TestCase
{
    public function testToText(): void
    {
        $cpu = '2.7GHz';
        $os = 'Ubuntu 12.04';
        $expectation = '"2.7GHz" "Ubuntu 12.04"';
        $hinfo = new HINFO();
        $hinfo->setCpu($cpu);
        $hinfo->setOs($os);

        $this->assertEquals($expectation, $hinfo->toText());
    }

    public function testGetters(): void
    {
        $cpu = '2.7GHz';
        $os = 'Ubuntu 12.04';
        $hinfo = new HINFO();
        $hinfo->setCpu($cpu);
        $hinfo->setOs($os);

        $this->assertEquals($cpu, $hinfo->getCpu());
        $this->assertEquals($os, $hinfo->getOs());
    }

    public function testGetType(): void
    {
        $hinfo = new HINFO();
        $this->assertEquals('HINFO', $hinfo->getType());
    }

    public function testGetTypeCode(): void
    {
        $hinfo = new HINFO();
        $this->assertEquals(13, $hinfo->getTypeCode());
    }

    public function testFromWire(): void
    {
        $hinfo = new HINFO();
        $hinfo->fromWire('"2.7GHz" "Ubuntu 12.04"');
        $this->assertEquals('2.7GHz', $hinfo->getCpu());
        $this->assertEquals('Ubuntu 12.04', $hinfo->getOs());
    }

    public function testFromText(): void
    {
        $hinfo = new HINFO();
        $hinfo->fromText('2.7GHz "Ubuntu 12.04"');
        $this->assertEquals('2.7GHz', $hinfo->getCpu());
        $this->assertEquals('Ubuntu 12.04', $hinfo->getOs());
    }

    public function testToWire(): void
    {
        $cpu = '2.7GHz';
        $os = 'Ubuntu 12.04';
        $expectation = '"2.7GHz" "Ubuntu 12.04"';
        $hinfo = new HINFO();
        $hinfo->setCpu($cpu);
        $hinfo->setOs($os);

        $this->assertEquals($expectation, $hinfo->toWire());
    }

    public function testFactory(): void
    {
        $hinfo = Factory::HINFO('SGI-IRIS-INDY', 'IRIX');
        $this->assertEquals('SGI-IRIS-INDY', $hinfo->getCpu());
        $this->assertEquals('IRIX', $hinfo->getOs());
    }
}
