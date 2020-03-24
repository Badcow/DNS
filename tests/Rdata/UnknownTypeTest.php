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

use Badcow\DNS\Rdata\UnknownType;
use PHPUnit\Framework\TestCase;

class UnknownTypeTest extends TestCase
{
    public function testToText(): void
    {
        $expectation = '\# 32 9aa065581e1a247d5e884a44adfa7cb4a849c7b90ade83c8fb9eae5984ea7fba';
        $uk = new UnknownType();
        $uk->setTypeCode(1234);
        $uk->setData(hex2bin('9aa065581e1a247d5e884a44adfa7cb4a849c7b90ade83c8fb9eae5984ea7fba'));

        $this->assertEquals($expectation, $uk->toText());
    }

    public function testFromText(): void
    {
        $uk = new UnknownType();
        $uk->fromText('\# 32 9aa065581e1a247d5e884a44adfa7cb4a849c7b90ade83c8fb9eae5984ea7fba');
        $this->assertEquals(hex2bin('9aa065581e1a247d5e884a44adfa7cb4a849c7b90ade83c8fb9eae5984ea7fba'), $uk->getData());
    }

    public function testWire(): void
    {
        $expectation = '\# 32 9aa065581e1a247d5e884a44adfa7cb4a849c7b90ade83c8fb9eae5984ea7fba';
        $wireFormat = hex2bin('9aa065581e1a247d5e884a44adfa7cb4a849c7b90ade83c8fb9eae5984ea7fba');
        $uk = new UnknownType();
        $uk->fromWire($wireFormat);

        $this->assertEquals($expectation, $uk->toText());
        $this->assertEquals($wireFormat, $uk->toWire());
    }
}
