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
use Badcow\DNS\Rdata\SRV;
use PHPUnit\Framework\TestCase;

class SrvTest extends TestCase
{
    public function testOutput(): void
    {
        $srv = Factory::SRV(10, 20, 666, 'doom.example.com.');

        $expectation = '10 20 666 doom.example.com.';

        $this->assertEquals($expectation, $srv->toText());
        $this->assertEquals(10, $srv->getPriority());
        $this->assertEquals(20, $srv->getWeight());
        $this->assertEquals(666, $srv->getPort());
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function testPortException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Port must be an unsigned integer on the range [0-65535]');

        $srv = new SRV();
        $srv->setPort(SRV::HIGHEST_PORT + 1);
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function testPriorityException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Priority must be an unsigned integer on the range [0-65535]');

        $srv = new SRV();
        $srv->setPriority(SRV::MAX_PRIORITY + 1);
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function testWeightException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Weight must be an unsigned integer on the range [0-65535]');

        $srv = new SRV();
        $srv->setWeight(SRV::MAX_WEIGHT + 1);
    }

    public function testFromText(): void
    {
        $text = '0 1 80 www.example.com.';
        $srv = new SRV();
        $srv->setPriority(0);
        $srv->setWeight(1);
        $srv->setPort(80);
        $srv->setTarget('www.example.com.');

        $this->assertEquals($srv, SRV::fromText($text));
    }

    public function testWire(): void
    {
        $expectation = pack('nnn', 0, 1, 80).chr(3).'www'.chr(7).'example'.chr(3).'com'.chr(0);
        $srv = new SRV();
        $srv->setPriority(0);
        $srv->setWeight(1);
        $srv->setPort(80);
        $srv->setTarget('www.example.com.');

        $this->assertEquals($expectation, $srv->toWire());
        $this->assertEquals($srv, SRV::fromWire($expectation));
    }
}
