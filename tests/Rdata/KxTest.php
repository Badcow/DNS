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
use Badcow\DNS\Rdata\KX;
use PHPUnit\Framework\TestCase;

class KxTest extends TestCase
{
    public function testSetters(): void
    {
        $target = 'foo.example.com.';
        $preference = 10;
        $kx = new KX();
        $kx->setExchanger($target);
        $kx->setPreference($preference);

        $this->assertEquals($target, $kx->getExchanger());
        $this->assertEquals($preference, $kx->getPreference());
    }

    public function testOutput(): void
    {
        $target = 'foo.example.com.';
        $kx = new KX();
        $kx->SetExchanger($target);
        $kx->setPreference(42);

        $this->assertEquals('42 foo.example.com.', $kx->toText());
    }

    public function testOutputThrowsExceptionWhenMissingPreference(): void
    {
        $kx = new KX();
        $kx->setExchanger('mail.google.com.');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No preference has been set on KX object.');
        $kx->toText();
    }

    public function testOutputThrowsExceptionWhenMissingExchanger(): void
    {
        $kx = new KX();
        $kx->setPreference(15);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No exchanger has been set on KX object.');
        $kx->toText();
    }

    public function testFactory(): void
    {
        $kx = Factory::KX(15, 'mx.example.com.');
        $this->assertInstanceOf(KX::class, $kx);
        $this->assertEquals('15 mx.example.com.', $kx->toText());
    }

    public function testFromText(): void
    {
        $text = '10 mail.example.com.';
        /** @var KX $kx */
        $kx = new KX();
        $kx->fromText($text);

        $this->assertEquals(10, $kx->getPreference());
        $this->assertEquals('mail.example.com.', $kx->getExchanger());
    }

    public function testWire(): void
    {
        $kx = new KX();
        $kx->setExchanger('mail.example.com.');
        $kx->setPreference(10);

        $expectation = pack('n', 10).chr(4).'mail'.chr(7).'example'.chr(3).'com'.chr(0);

        $this->assertEquals($expectation, $kx->toWire());
        $fromWire = new KX();
        $fromWire->fromWire($expectation);
        $this->assertEquals($kx, $fromWire);
    }
}
