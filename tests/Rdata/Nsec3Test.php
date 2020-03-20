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
use Badcow\DNS\Rdata\A;
use Badcow\DNS\Rdata\Factory;
use Badcow\DNS\Rdata\NSEC3;
use Badcow\DNS\Rdata\RRSIG;
use Badcow\DNS\Rdata\UnsupportedTypeException;
use PHPUnit\Framework\TestCase;

class Nsec3Test extends TestCase
{
    public function testGetType(): void
    {
        $nsec3 = new NSEC3();
        $this->assertEquals('NSEC3', $nsec3->getType());
    }

    public function testGetTypeCode(): void
    {
        $nsec3 = new NSEC3();
        $this->assertEquals(50, $nsec3->getTypeCode());
    }

    public function testToText(): void
    {
        $nsec3 = new NSEC3();
        $nsec3->setHashAlgorithm(Algorithms::RSAMD5);
        $nsec3->setUnsignedDelegationsCovered(true);
        $nsec3->setIterations(12);
        $nsec3->setSalt('aabbccdd');
        $nsec3->setNextHashedOwnerName('2vptu5timamqttgl4luu9kg21e0aor3s');
        $nsec3->addType(A::TYPE);
        $nsec3->addType(RRSIG::TYPE);

        $this->assertEquals('1 1 12 aabbccdd 2vptu5timamqttgl4luu9kg21e0aor3s A RRSIG', $nsec3->toText());
    }

    /**
     * @throws UnsupportedTypeException
     */
    public function testWire(): void
    {
        $nsec3 = new NSEC3();
        $nsec3->setHashAlgorithm(Algorithms::RSAMD5);
        $nsec3->setUnsignedDelegationsCovered(true);
        $nsec3->setIterations(12);
        $nsec3->setSalt('aabbccdd');
        $nsec3->setNextHashedOwnerName('2vptu5timamqttgl4luu9kg21e0aor3s');
        $nsec3->addType(A::TYPE);
        $nsec3->addType(RRSIG::TYPE);

        $wireFormat = $nsec3->toWire();

        $this->assertEquals($nsec3, NSEC3::fromWire($wireFormat));
    }

    public function testFromText(): void
    {
        $expectation = new NSEC3();
        $expectation->setHashAlgorithm(Algorithms::RSAMD5);
        $expectation->setUnsignedDelegationsCovered(true);
        $expectation->setIterations(12);
        $expectation->setSalt('aabbccdd');
        $expectation->setNextHashedOwnerName('2vptu5timamqttgl4luu9kg21e0aor3s');
        $expectation->addType(A::TYPE);
        $expectation->addType(RRSIG::TYPE);

        $this->assertEquals($expectation, NSEC3::fromText('1 1 12 aabbccdd 2vptu5timamqttgl4luu9kg21e0aor3s A RRSIG'));
    }

    public function testFactory(): void
    {
        $nsec3 = Factory::NSEC3(Algorithms::RSAMD5, true, 12, 'aabbccdd', '2vptu5timamqttgl4luu9kg21e0aor3s', ['A', 'RRSIG']);
        $this->assertEquals('1 1 12 aabbccdd 2vptu5timamqttgl4luu9kg21e0aor3s A RRSIG', $nsec3->toText());
    }
}
