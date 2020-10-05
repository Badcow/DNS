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
use Badcow\DNS\Rdata\RP;
use PHPUnit\Framework\TestCase;

class RpTest extends TestCase
{
    public function testFactory(): void
    {
        $expectation = 'louie.trantor.umd.edu. lam1.people.umd.edu.';
        $rp = Factory::RP('louie.trantor.umd.edu.', 'lam1.people.umd.edu.');

        $this->assertEquals($expectation, $rp->toText());
    }

    public function testFromText(): void
    {
        $rp = new RP();
        $rp->fromText('louie.trantor.umd.edu. lam1.people.umd.edu.');

        $this->assertEquals('louie.trantor.umd.edu.', $rp->getMailboxDomainName());
        $this->assertEquals('lam1.people.umd.edu.', $rp->getTxtDomainName());
    }

    public function testToText(): void
    {
        $expectation = 'louie.trantor.umd.edu. lam1.people.umd.edu.';
        $rp = new RP();
        $rp->setMailboxDomainName('louie.trantor.umd.edu.');
        $rp->setTxtDomainName('lam1.people.umd.edu.');

        $this->assertEquals($expectation, $rp->toText());
    }

    public function testWire(): void
    {
        $expectation = chr(5).'louie'.chr(7).'trantor'.chr(3).'umd'.chr(3).'edu'.chr(0).
            chr(4).'lam1'.chr(6).'people'.chr(3).'umd'.chr(3).'edu'.chr(0);

        $rp = new RP();
        $rp->setMailboxDomainName('louie.trantor.umd.edu.');
        $rp->setTxtDomainName('lam1.people.umd.edu.');

        $this->assertEquals($expectation, $rp->toWire());
        $fromWire = new RP();
        $fromWire->fromWire($expectation);
        $this->assertEquals($rp, $fromWire);
    }
}
