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

namespace Badcow\DNS\Tests\Edns\Option;

use Badcow\DNS\Edns\Option\DecodeException;
use Badcow\DNS\Edns\Option\TCP_KEEPALIVE;
use PHPUnit\Framework\TestCase;

class TCP_KEEPALIVETest extends TestCase
{
    /**
     * @var TCP_KEEPALIVE
     */
    private $option;

    public function setUp(): void
    {
        $this->option = new TCP_KEEPALIVE();
    }

    public function testGetterSetters(): void
    {
        $this->assertEquals('TCP_KEEPALIVE', $this->option->getName());
    }

    public function testToWire(): void
    {
        $noLimit = new TCP_KEEPALIVE();
        $this->assertEquals('', $noLimit->toWire());

        $withLimit = new TCP_KEEPALIVE();
        $withLimit->setTimeout(1000);
        $this->assertEquals("\x03\xE8", $withLimit->toWire());
    }

    public function testFromWire1(): void
    {
        $wire = '';
        $noLimit = new TCP_KEEPALIVE();
        $noLimit->fromWire($wire);
        $this->assertNull($noLimit->getTimeout());
    }

    public function testFromWire2(): void
    {
        $this->expectException(DecodeException::class);
        $wire = "\xFF\xFF\xFF";
        $wrongTimeout = new TCP_KEEPALIVE();
        $wrongTimeout->fromWire($wire);
    }

    public function testFromWire3(): void
    {
        $wire = "\x07\xD0";
        $withLimit = new TCP_KEEPALIVE();
        $withLimit->fromWire($wire);
        $this->assertEquals(2000, $withLimit->getTimeout());
    }
}
