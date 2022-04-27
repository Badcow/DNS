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

use Badcow\DNS\Edns\Option\CLIENT_SUBNET;
use PHPUnit\Framework\TestCase;

class CLIENT_SUBNETTest extends TestCase
{
    /**
     * @var CLIENT_SUBNET
     */
    private $option;

    public function setUp(): void
    {
        $this->option = new CLIENT_SUBNET();
    }

    public function testGetterSetters(): void
    {
        $this->assertEquals('CLIENT_SUBNET', $this->option->getName());
    }

    public function testToWire(): void
    {
        $address = '200.100.50.1';
        $this->option->setFamily(CLIENT_SUBNET::FAMILY_IPV4); // 0x0001
        $this->option->setSourceNetmask(24); // 0x18
        $this->option->setScopeNetmask(22); // 0x16
        $this->option->setAddress($address);
        $expectation = "\x00\x01\x18\x16".inet_pton($address);

        $this->assertEquals($expectation, $this->option->toWire());
    }

    public function testFromWire(): void
    {
        $address = '200.100.50.1';
        $wire = "\x00\x01\x18\x16".inet_pton($address);
        $option = new CLIENT_SUBNET();
        $option->fromWire($wire);
        $this->assertEquals(CLIENT_SUBNET::FAMILY_IPV4, $option->getFamily());
        $this->assertEquals(24, $option->getSourceNetmask());
        $this->assertEquals(22, $option->getScopeNetmask());
        $this->assertEquals($address, $option->getAddress());
    }
}
