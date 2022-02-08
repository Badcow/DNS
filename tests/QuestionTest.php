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

namespace Badcow\DNS\Tests;

use Badcow\DNS\Question;
use Badcow\DNS\Rdata\UnsupportedTypeException;
use PHPUnit\Framework\TestCase;

class QuestionTest extends TestCase
{
    /**
     * @throws UnsupportedTypeException
     */
    public function testToWire(): void
    {
        $q = new Question();
        $q->setName('example.com.');
        $q->setType('NS');
        $q->setClass('IN');

        $expectation = chr(7).'example'.chr(3).'com'.chr(0).pack('nn', 2, 1);

        $this->assertEquals($expectation, $q->toWire());
    }

    public function testSetClassThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid class: "65536".');
        $q = new Question();
        $q->setClassId(65536);
    }

    public function testSetNameThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('"abc123.com" is not a fully qualified domain name.');
        $q = new Question();
        $q->setName('abc123.com');
    }

    public function testFromWire(): void
    {
        $wireFormat = chr(7).'example'.chr(3).'com'.chr(0).pack('nn', 2, 1);

        $q = Question::fromWire($wireFormat);

        $this->assertEquals('example.com.', $q->getName());
        $this->assertEquals('NS', $q->getType());
        $this->assertEquals('IN', $q->getClass());
    }

    public function testSetTypeCodeThrowsException(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('TypeCode must be an unsigned 16-bit integer. "65536" given.');
        $q = new Question();
        $q->setTypeCode(65536);
    }
}
