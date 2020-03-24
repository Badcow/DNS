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
use Badcow\DNS\Rdata\SPF;
use PHPUnit\Framework\TestCase;

class SpfTest extends TestCase
{
    public function testFromText(): void
    {
        $text = '"v=spf1 ip4:192.0.2.0/24 ip4:198.51.100.123 a -all"';

        /** @var SPF $spf */
        $spf = new SPF();
        $spf->fromText($text);

        $this->assertEquals('SPF', $spf->getType());
        $this->assertEquals('v=spf1 ip4:192.0.2.0/24 ip4:198.51.100.123 a -all', $spf->getText());
    }

    public function testToText(): void
    {
        $spf = new SPF();
        $spf->setText('v=spf1 ip4:192.0.2.0/24 ip4:198.51.100.123 a -all');

        $this->assertEquals('"v=spf1 ip4:192.0.2.0/24 ip4:198.51.100.123 a -all"', $spf->toText());
        $this->assertEquals('SPF', $spf->getType());
    }

    public function testWire(): void
    {
        $wireFormat = chr(49).'v=spf1 ip4:192.0.2.0/24 ip4:198.51.100.123 a -all';
        $offset = 1;
        $rdLength = 49;

        $spf = new SPF();
        $spf->setText('v=spf1 ip4:192.0.2.0/24 ip4:198.51.100.123 a -all');

        $fromWire = new SPF();
        $fromWire->fromWire($wireFormat, $offset, $rdLength);
        $this->assertEquals($spf, $fromWire);
    }

    public function testFactory(): void
    {
        $wireFormat = 'v=spf1 ip4:192.0.2.0/24 ip4:198.51.100.123 a -all';

        $spf = new SPF();
        $spf->setText($wireFormat);

        $this->assertEquals($spf, Factory::SPF($wireFormat));
    }
}
