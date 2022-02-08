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

use Badcow\DNS\Edns\Option\COOKIE;
use Badcow\DNS\Edns\Option\DecodeException;
use PHPUnit\Framework\TestCase;

class COOKIETest extends TestCase
{
    /**
     * @var COOKIE
     */
    private $option;

    public function setUp(): void
    {
        $this->option = new COOKIE();
    }

    public function testGetterSetters(): void
    {
        $this->assertEquals('COOKIE', $this->option->getName());
    }

    public function testSetClientCookie(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->option->setClientCookie(str_repeat('a', 9));
    }

    public function testSetServerCookie(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->option->setServerCookie(str_repeat('b', 7));
    }

    public function testToWire(): void
    {
        $noCookie = new COOKIE();
        $this->assertEquals('', $noCookie->toWire());

        $justClientCookie = new COOKIE();
        $justClientCookie->setClientCookie('aaaaaaaa');
        $this->assertEquals('aaaaaaaa', $justClientCookie->toWire());

        $bothCookies = new COOKIE();
        $bothCookies->setClientCookie('aaaaaaaa');
        $bothCookies->setServerCookie('bbbbbbbbb');
        $this->assertEquals('aaaaaaaabbbbbbbbb', $bothCookies->toWire());
    }

    public function testFromWire1(): void
    {
        $this->expectException(DecodeException::class);
        $wire = '';
        $noCookie = new COOKIE();
        $noCookie->fromWire($wire);
    }

    public function testFromWire2(): void
    {
        $wire = 'aaaaaaaa';
        $justClientCookie = new COOKIE();
        $justClientCookie->fromWire($wire);
        $this->assertEquals('aaaaaaaa', $justClientCookie->getClientCookie());
        $this->assertNull($justClientCookie->getServerCookie());
    }

    public function testFromWire3(): void
    {
        $wire = 'aaaaaaaabbbbbbbbb';
        $bothCookies = new COOKIE();
        $bothCookies->fromWire($wire);
        $this->assertEquals('aaaaaaaa', $bothCookies->getClientCookie());
        $this->assertEquals('bbbbbbbbb', $bothCookies->getServerCookie());
    }
}
