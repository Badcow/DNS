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

use Badcow\DNS\Rdata\LOC;
use PHPUnit\Framework\TestCase;

class LocTest extends TestCase
{
    public function testOutput(): void
    {
        $rdata = new LOC();
        $rdata->setLatitude(-35.3075);
        $rdata->setLongitude(149.1244);
        $rdata->setAltitude(500);
        $rdata->setSize(20.12);
        $rdata->setHorizontalPrecision(200.3);
        $rdata->setVerticalPrecision(300.1);

        $expected = '35 18 27.000 S 149 7 27.840 E 500.00m 20.12m 200.30m 300.10m';
        $this->assertEquals($expected, $rdata->toText());
    }

    public function testSetLatitude(): void
    {
        $latitude = -35.3075;
        $lat_dms = '35 18 27.000 S';

        $rdata = new LOC();
        $rdata->setLatitude($latitude);

        $this->assertEquals($latitude, $rdata->getLatitude());
        $this->assertEquals($latitude, $rdata->getLatitude(LOC::FORMAT_DECIMAL));
        $this->assertEquals($lat_dms, $rdata->getLatitude(LOC::FORMAT_DMS));
    }

    public function testSetLongitude(): void
    {
        $longitude = 149.1244;
        $lon_dms = '149 7 27.840 E';

        $rdata = new LOC();
        $rdata->setLongitude($longitude);

        $this->assertEquals($longitude, $rdata->getLongitude());
        $this->assertEquals($longitude, $rdata->getLongitude(LOC::FORMAT_DECIMAL));
        $this->assertEquals($lon_dms, $rdata->getLongitude(LOC::FORMAT_DMS));
    }

    /**
     * @throws \OutOfRangeException
     */
    public function testSetAltitude1(): void
    {
        $this->expectException(\OutOfRangeException::class);
        $this->expectExceptionMessage('The altitude must be on [-100000.00, 42849672.95].');

        $rdata = new LOC();
        $rdata->setAltitude(-100001);
    }

    /**
     * @throws \OutOfRangeException
     */
    public function testSetAltitude2(): void
    {
        $this->expectException(\OutOfRangeException::class);
        $this->expectExceptionMessage('The altitude must be on [-100000.00, 42849672.95].');

        $rdata = new LOC();
        $rdata->setAltitude(42849673);
    }

    public function testGetAltitude(): void
    {
        $rdata = new LOC();
        $altitude = 12345;
        $rdata->setAltitude($altitude);
        $this->assertEquals($altitude, $rdata->getAltitude());
    }

    /**
     * @thows \OutOfRangeException
     */
    public function testSetSize1(): void
    {
        $this->expectException(\OutOfRangeException::class);
        $this->expectExceptionMessage('The size must be on [0, 9e9].');

        $rdata = new LOC();
        $rdata->setSize(-1);
    }

    /**
     * @throws \OutOfRangeException
     */
    public function testSetSize2(): void
    {
        $this->expectException(\OutOfRangeException::class);
        $this->expectExceptionMessage('The size must be on [0, 9e9].');

        $rdata = new LOC();
        $rdata->setSize(9000000002);
    }

    public function testGetSize(): void
    {
        $size = 1231;
        $rdata = new LOC();
        $rdata->setSize($size);
        $this->assertEquals($size, $rdata->getSize());
    }

    /**
     * @throws \OutOfRangeException
     */
    public function testSetVerticalPrecision1(): void
    {
        $this->expectException(\OutOfRangeException::class);
        $this->expectExceptionMessage('The vertical precision must be on [0, 9e9].');

        $rdata = new LOC();
        $rdata->setVerticalPrecision(-1);
    }

    /**
     * @throws \OutOfRangeException
     */
    public function testSetVerticalPrecision2(): void
    {
        $this->expectException(\OutOfRangeException::class);
        $this->expectExceptionMessage('The vertical precision must be on [0, 9e9].');

        $rdata = new LOC();
        $rdata->setVerticalPrecision(9000000002);
    }

    /**
     * @throws \OutOfRangeException
     */
    public function testSetHorizontalPrecision1(): void
    {
        $this->expectException(\OutOfRangeException::class);
        $this->expectExceptionMessage('The horizontal precision must be on [0, 9e9].');

        $rdata = new LOC();
        $rdata->setHorizontalPrecision(-1);
    }

    /**
     * @throws \OutOfRangeException
     */
    public function testSetHorizontalPrecision2(): void
    {
        $this->expectException(\OutOfRangeException::class);
        $this->expectExceptionMessage('The horizontal precision must be on [0, 9e9].');

        $rdata = new LOC();
        $rdata->setHorizontalPrecision(9000000002);
    }

    public function testGetHorizontalPrecision(): void
    {
        $hp = 127835;
        $rdata = new LOC();
        $rdata->setHorizontalPrecision($hp);
        $this->assertEquals($hp, $rdata->getHorizontalPrecision());
    }

    public function testGetVerticalPrecision(): void
    {
        $vp = 127835;
        $rdata = new LOC();
        $rdata->setVerticalPrecision($vp);
        $this->assertEquals($vp, $rdata->getVerticalPrecision());
    }

    public function testWire(): void
    {
        $loc = new LOC();
        $loc->setLatitude(-35.3075);
        $loc->setLongitude(149.1244);
        $loc->setAltitude(500);
        $loc->setSize(200);
        $loc->setHorizontalPrecision(200);
        $loc->setVerticalPrecision(300);

        $wireFormat = 'abc'.$loc->toWire();
        $offset = 3;

        $fromWire = new LOC();
        $fromWire->fromWire($wireFormat, $offset);
        $this->assertEquals($loc, $fromWire);
        $this->assertEquals(19, $offset);
    }
}
