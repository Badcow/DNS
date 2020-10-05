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
use Badcow\DNS\Rdata\DLV;
use Badcow\DNS\Rdata\Factory;
use PHPUnit\Framework\TestCase;

class DlvTest extends TestCase
{
    private static $digest = '2BB183AF5F22588179A53B0A98631FAD1A292118';

    public function testFactory(): void
    {
        $keyTag = 60485;
        $ds = Factory::DLV($keyTag, Algorithms::RSASHA1, hex2bin(self::$digest), DLV::DIGEST_SHA1);

        $this->assertEquals($keyTag, $ds->getKeyTag());
        $this->assertEquals(Algorithms::RSASHA1, $ds->getAlgorithm());
        $this->assertEquals(hex2bin(self::$digest), $ds->getDigest());
        $this->assertEquals(DLV::DIGEST_SHA1, $ds->getDigestType());
    }

    public function testFromText(): void
    {
        $expectation = new DLV();
        $expectation->setKeyTag(60485);
        $expectation->setAlgorithm(Algorithms::RSASHA1);
        $expectation->setDigestType(DLV::DIGEST_SHA1);
        $expectation->setDigest(hex2bin(self::$digest));

        $dlv = new DLV();
        $dlv->fromText('60485 5 1 '.self::$digest);
        $this->assertInstanceOf(DLV::class, $dlv);
        $this->assertEquals($expectation, $dlv);
    }

    public function testWire(): void
    {
        $dlv = new DLV();
        $dlv->setKeyTag(60485);
        $dlv->setAlgorithm(Algorithms::RSASHA1);
        $dlv->setDigestType(DLV::DIGEST_SHA1);
        $dlv->setDigest(hex2bin(self::$digest));
        $wireFormat = $dlv->toWire();
        $fromWire = new DLV();
        $fromWire->fromWire($wireFormat);

        $this->assertInstanceOf(DLV::class, $fromWire);
        $this->assertEquals($dlv, $fromWire);
    }
}
