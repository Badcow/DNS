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
use Badcow\DNS\Rdata\DS;
use Badcow\DNS\Rdata\Factory;
use PHPUnit\Framework\TestCase;

class DsTest extends TestCase
{
    private static $digest = '2BB183AF5F22588179A53B0A98631FAD1A292118';

    public function testOutput(): void
    {
        $expectation = '60485 5 1 '.self::$digest;

        $ds = new DS();
        $ds->setKeyTag(60485);
        $ds->setAlgorithm(Algorithms::RSASHA1);
        $ds->setDigestType(DS::DIGEST_SHA1);
        $ds->setDigest(self::$digest);

        $this->assertEquals($expectation, $ds->toText());
    }

    public function testFactory(): void
    {
        $keyTag = 60485;
        $ds = Factory::DS($keyTag, Algorithms::RSASHA1, self::$digest, DS::DIGEST_SHA1);

        $this->assertEquals($keyTag, $ds->getKeyTag());
        $this->assertEquals(Algorithms::RSASHA1, $ds->getAlgorithm());
        $this->assertEquals(self::$digest, $ds->getDigest());
        $this->assertEquals(DS::DIGEST_SHA1, $ds->getDigestType());
    }

    public function testFromText(): void
    {
        $expectation = new DS();
        $expectation->setKeyTag(60485);
        $expectation->setAlgorithm(Algorithms::RSASHA1);
        $expectation->setDigestType(DS::DIGEST_SHA1);
        $expectation->setDigest(self::$digest);

        $this->assertEquals($expectation, DS::fromText('60485 5 1 '.self::$digest));
    }

    public function testWire(): void
    {
        $ds = new DS();
        $ds->setKeyTag(60485);
        $ds->setAlgorithm(Algorithms::RSASHA1);
        $ds->setDigestType(DS::DIGEST_SHA1);
        $ds->setDigest(self::$digest);
        $wireFormat = $ds->toWire();

        $this->assertEquals($ds, DS::fromWire($wireFormat));
    }
}
