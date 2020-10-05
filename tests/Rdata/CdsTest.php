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

use Badcow\DNS\Algorithms;
use Badcow\DNS\Rdata\CDS;
use Badcow\DNS\Rdata\Factory;
use PHPUnit\Framework\TestCase;

class CdsTest extends TestCase
{
    private static $digest = '2BB183AF5F22588179A53B0A98631FAD1A292118';

    public function testFactory(): void
    {
        $keyTag = 60485;
        $ds = Factory::CDS($keyTag, Algorithms::RSASHA1, hex2bin(self::$digest), CDS::DIGEST_SHA1);

        $this->assertEquals($keyTag, $ds->getKeyTag());
        $this->assertEquals(Algorithms::RSASHA1, $ds->getAlgorithm());
        $this->assertEquals(hex2bin(self::$digest), $ds->getDigest());
        $this->assertEquals(CDS::DIGEST_SHA1, $ds->getDigestType());
    }

    public function testFromText(): void
    {
        $expectation = new CDS();
        $expectation->setKeyTag(60485);
        $expectation->setAlgorithm(Algorithms::RSASHA1);
        $expectation->setDigestType(CDS::DIGEST_SHA1);
        $expectation->setDigest(hex2bin(self::$digest));

        $cds = new CDS();
        $cds->fromText('60485 5 1 '.self::$digest);
        $this->assertInstanceOf(CDS::class, $cds);
        $this->assertEquals($expectation, $cds);
    }

    public function testWire(): void
    {
        $cds = new CDS();
        $cds->setKeyTag(60485);
        $cds->setAlgorithm(Algorithms::RSASHA1);
        $cds->setDigestType(CDS::DIGEST_SHA1);
        $cds->setDigest(hex2bin(self::$digest));
        $wireFormat = $cds->toWire();
        $fromWire = new CDS();
        $fromWire->fromWire($wireFormat);

        $this->assertInstanceOf(CDS::class, $fromWire);
        $this->assertEquals($cds, $fromWire);
    }
}
