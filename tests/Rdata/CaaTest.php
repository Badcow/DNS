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

use Badcow\DNS\Rdata\CAA;
use Badcow\DNS\Rdata\Factory;
use PHPUnit\Framework\TestCase;

class CaaTest extends TestCase
{
    public function testOutput(): void
    {
        $caa = Factory::CAA(0, 'issue', 'letsencrypt.org');

        $expectation = '0 issue "letsencrypt.org"';

        $this->assertEquals($expectation, $caa->toText());
        $this->assertEquals(0, $caa->getFlag());
        $this->assertEquals('issue', $caa->getTag());
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function testFlagException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Flag must be an unsigned 8-bit integer.');

        $srv = new CAA();
        $srv->setFlag(256);
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function testTagException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Tag can be one of this type "issue", "issuewild", or "iodef".');

        $srv = new CAA();
        $srv->setTag('not_exist');
    }

    public function testGetType(): void
    {
        $this->assertEquals('CAA', (new CAA())->getType());
    }

    public function testFromText(): void
    {
        $text = '0 iodef "mailto:security@example.com"';
        /** @var CAA $caa */
        $caa = new CAA();
        $caa->fromText($text);

        $this->assertEquals(0, $caa->getFlag());
        $this->assertEquals(CAA::TAG_IODEF, $caa->getTag());
        $this->assertEquals('mailto:security@example.com', $caa->getValue());
    }

    public function testWire(): void
    {
        $expectation = chr(0).chr(5).'iodef'.'mailto:security@example.com';
        $caa = new CAA();
        $caa->setFlag(0);
        $caa->setTag(CAA::TAG_IODEF);
        $caa->setValue('mailto:security@example.com');

        $fromWire = new CAA();
        $fromWire->fromWire($expectation);

        $this->assertEquals($expectation, $caa->toWire());
        $this->assertEquals($caa, $fromWire);
    }

    public function testToWireThrowsExceptionIfNotAllParametersAreSet(): void
    {
        $caa = new CAA();
        $caa->setTag(CAA::TAG_IODEF);
        $caa->setValue('mailto:security@example.com');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('All CAA parameters must be set.');
        $caa->toWire();
    }

    public function testToTextThrowsExceptionIfNotAllParametersAreSet(): void
    {
        $caa = new CAA();
        $caa->setFlag(0);
        $caa->setTag(CAA::TAG_IODEF);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('All CAA parameters must be set.');
        $caa->toText();
    }
}
