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
use Badcow\DNS\Rdata\NSEC3PARAM;
use PHPUnit\Framework\TestCase;

class Nsec3paramTest extends TestCase
{
    public function testGetType(): void
    {
        $nsec3param = new NSEC3PARAM();
        $this->assertEquals('NSEC3PARAM', $nsec3param->getType());
    }

    public function testGetTypeCode(): void
    {
        $nsec3param = new NSEC3PARAM();
        $this->assertEquals(51, $nsec3param->getTypeCode());
    }

    public function testToText(): void
    {
        $nsec3param = new NSEC3PARAM();
        $nsec3param->setHashAlgorithm(Algorithms::RSAMD5);
        $nsec3param->setSalt('d9143ec07c5977ae');
        $nsec3param->setIterations(55);
        $nsec3param->setFlags(0);

        $expectation = '1 0 55 d9143ec07c5977ae';
        $this->assertEquals($expectation, $nsec3param->toText());
    }

    public function testToWire(): void
    {
        $nsec3param = new NSEC3PARAM();
        $nsec3param->setHashAlgorithm(Algorithms::RSAMD5);
        $nsec3param->setSalt('d9143ec07c5977ae');
        $nsec3param->setIterations(55);
        $nsec3param->setFlags(0);

        $expectation = chr(1).chr(0).pack('n', 55).chr(8).hex2bin('d9143ec07c5977ae');

        $this->assertEquals($expectation, $nsec3param->toWire());
    }

    public function testFromText(): void
    {
        $expectation = new NSEC3PARAM();
        $expectation->setHashAlgorithm(Algorithms::RSAMD5);
        $expectation->setSalt('d9143ec07c5977ae');
        $expectation->setIterations(55);
        $expectation->setFlags(0);

        $fromText = new NSEC3PARAM();
        $fromText->fromText('1 0 55 d9143ec07c5977ae');
        $this->assertEquals($expectation, $fromText);
    }

    public function testFromWire(): void
    {
        $expectation = new NSEC3PARAM();
        $expectation->setHashAlgorithm(Algorithms::RSAMD5);
        $expectation->setSalt('d9143ec07c5977ae');
        $expectation->setIterations(55);
        $expectation->setFlags(0);

        $wireFormat = chr(1).chr(0).pack('n', 55).chr(8).hex2bin('d9143ec07c5977ae');

        $fromWire = new NSEC3PARAM();
        $fromWire->fromWire($wireFormat);
        $this->assertEquals($expectation, $fromWire);
    }

    public function testFactory(): void
    {
        $nsec3param = Factory::NSEC3PARAM(Algorithms::RSAMD5, 0, 55, 'd9143ec07c5977ae');

        $this->assertEquals(1, $nsec3param->getHashAlgorithm());
        $this->assertEquals(0, $nsec3param->getFlags());
        $this->assertEquals(55, $nsec3param->getIterations());
        $this->assertEquals('d9143ec07c5977ae', $nsec3param->getSalt());
    }
}
