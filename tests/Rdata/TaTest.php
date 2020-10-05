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
use Badcow\DNS\Rdata\Factory;
use Badcow\DNS\Rdata\TA;
use PHPUnit\Framework\TestCase;

class TaTest extends TestCase
{
    private static $digest = '2BB183AF5F22588179A53B0A98631FAD1A292118';

    public function testOutput(): void
    {
        $expectation = '60485 5 1 '.self::$digest;

        $ta = new TA();
        $ta->setKeyTag(60485);
        $ta->setAlgorithm(Algorithms::RSASHA1);
        $ta->setDigestType(TA::DIGEST_SHA1);
        $ta->setDigest(hex2bin(self::$digest));

        $this->assertEquals($expectation, $ta->toText());
    }

    public function testFactory(): void
    {
        $keyTag = 60485;
        $ta = Factory::TA($keyTag, Algorithms::RSASHA1, self::$digest, TA::DIGEST_SHA1);

        $this->assertEquals($keyTag, $ta->getKeyTag());
        $this->assertEquals(Algorithms::RSASHA1, $ta->getAlgorithm());
        $this->assertEquals(self::$digest, $ta->getDigest());
        $this->assertEquals(TA::DIGEST_SHA1, $ta->getDigestType());
    }

    public function testFromText(): void
    {
        $expectation = new TA();
        $expectation->setKeyTag(60485);
        $expectation->setAlgorithm(Algorithms::RSASHA1);
        $expectation->setDigestType(TA::DIGEST_SHA1);
        $expectation->setDigest(hex2bin(self::$digest));

        $fromText = new TA();
        $fromText->fromText('60485 5 1 '.self::$digest);
        $this->assertEquals($expectation, $fromText);
    }

    public function testWire(): void
    {
        $ta = new TA();
        $ta->setKeyTag(60485);
        $ta->setAlgorithm(Algorithms::RSASHA1);
        $ta->setDigestType(TA::DIGEST_SHA1);
        $ta->setDigest(hex2bin(self::$digest));
        $wireFormat = $ta->toWire();

        $fromWire = new TA();
        $fromWire->fromWire($wireFormat);
        $this->assertEquals($ta, $fromWire);
    }
}
