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

use Badcow\DNS\Rdata\MX;
use PHPUnit\Framework\TestCase;

class MxTest extends TestCase
{
    public function testSetters(): void
    {
        $target = 'foo.example.com.';
        $preference = 10;
        $mx = new MX();
        $mx->setExchange($target);
        $mx->setPreference($preference);

        $this->assertEquals($target, $mx->getExchange());
        $this->assertEquals($preference, $mx->getPreference());
    }

    public function testOutput(): void
    {
        $target = 'foo.example.com.';
        $mx = new MX();
        $mx->SetExchange($target);
        $mx->setPreference(42);

        $this->assertEquals('42 foo.example.com.', $mx->toText());
    }

    public function testOutputThrowsExceptionWhenMissingPreference(): void
    {
        $mx = new MX();
        $mx->setExchange('mail.google.com.');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No preference has been set on MX object.');
        $mx->toText();
    }

    public function testOutputThrowsExceptionWhenMissingExchange(): void
    {
        $mx = new MX();
        $mx->setPreference(15);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No exchange has been set on MX object.');
        $mx->toText();
    }

    public function testFromText(): void
    {
        $text = '10 mail.example.com.';
        /** @var MX $mx */
        $mx = new MX();
        $mx->fromText($text);

        $this->assertEquals(10, $mx->getPreference());
        $this->assertEquals('mail.example.com.', $mx->getExchange());
    }

    public function testWire(): void
    {
        $mx = new MX();
        $mx->setExchange('mail.example.com.');
        $mx->setPreference(10);

        $expectation = pack('n', 10).chr(4).'mail'.chr(7).'example'.chr(3).'com'.chr(0);

        $this->assertEquals($expectation, $mx->toWire());
        $fromWire = new MX();
        $fromWire->fromWire($expectation);
        $this->assertEquals($mx, $fromWire);
    }
}
