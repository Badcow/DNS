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

use Badcow\DNS\Classes;
use Badcow\DNS\Rdata\A;
use Badcow\DNS\Rdata\Factory;
use Badcow\DNS\ResourceRecord;
use Badcow\DNS\UnsetValueException;
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ResourceRecordTest extends TestCase
{
    public function testSetClass(): void
    {
        $rr = new ResourceRecord();
        $rr->setClass(Classes::INTERNET);
        $this->assertEquals(Classes::INTERNET, $rr->getClass());

        $this->expectException(InvalidArgumentException::class);
        $rr->setClass('XX');
    }

    /**
     * Tests the getter and setter methods.
     */
    public function testSettersAndGetters(): void
    {
        $rr = new ResourceRecord();
        $name = 'test';
        $ttl = 3500;
        $comment = 'Hello';
        $a = Factory::A('192.168.7.7');

        $rr->setName($name);
        $rr->setClass(Classes::INTERNET);
        $rr->setRdata($a);
        $rr->setTtl($ttl);
        $rr->setComment($comment);

        $this->assertEquals($a, $rr->getRdata());
        $this->assertEquals($name, $rr->getName());
        $this->assertEquals($ttl, $rr->getTtl());
        $this->assertEquals($comment, $rr->getComment());
        $this->assertEquals($a->getType(), $rr->getType());
    }

    public function testUnsetTtl(): void
    {
        $rr = new ResourceRecord();
        $rr->setName('example.com.');
        $ttl = 10800;

        $this->assertNull($rr->getTtl());
        $rr->setTtl($ttl);
        $this->assertEquals($ttl, $rr->getTtl());
        $rr->setTtl(null);
        $this->assertNull($rr->getTtl());
    }

    /**
     * @throws Exception
     */
    public function testToWire(): void
    {
        $expectation = pack(
            'C*',
            0x03,
            0x61,
            0x62,
            0x63,
            0x07,
            0x65,
            0x78,
            0x61,
            0x6D,
            0x70,
            0x6C,
            0x65,
            0x03,
            0x63,
            0x6F,
            0x6D,
            0x00, //(3)abc(7)example(3)com(NULL)
            0x00,
            0x01, //A (1)
            0x00,
            0x01, //IN (1)
            0x00,
            0x00,
            0x0E,
            0x10, //3600
            0x00,
            0x04, //4 (RDLENGTH)
            0xC0,
            0xA8,
            0x01,
            0x01 //192.168.1.1
        );

        $a = Factory::A('192.168.1.1');
        $rr = new ResourceRecord();
        $rr->setName('abc.example.com.');
        $rr->setClass(Classes::INTERNET);
        $rr->setRdata($a);
        $rr->setTtl(3600);

        $this->assertEquals($expectation, $rr->toWire());
    }

    public function dataProviderForTestToWireThrowsExceptionsIfValuesAreNotSet(): array
    {
        $rr_noName = new ResourceRecord();
        $rr_noName->setClass(null);

        $rr_noRdata = clone $rr_noName;
        $rr_noRdata->setName('@');

        $rr_noClass = clone $rr_noRdata;
        $rr_noClass->setRdata(new A());

        $rr_noTtl = clone $rr_noClass;
        $rr_noTtl->setClass('CLASS42');

        $rr_unqualifiedName = clone $rr_noTtl;
        $rr_unqualifiedName->setTtl(4242);

        return [
            [$rr_noName, UnsetValueException::class, 'ResourceRecord name has not been set.'],
            [$rr_noRdata, UnsetValueException::class, 'ResourceRecord rdata has not been set.'],
            [$rr_noClass, UnsetValueException::class, 'ResourceRecord class has not been set.'],
            [$rr_noTtl, UnsetValueException::class, 'ResourceRecord TTL has not been set.'],
            [$rr_unqualifiedName, \InvalidArgumentException::class, '"@" is not a fully qualified domain name.'],
        ];
    }

    /**
     * @dataProvider dataProviderForTestToWireThrowsExceptionsIfValuesAreNotSet
     *
     * @throws UnsetValueException
     */
    public function testToWireThrowsExceptionsIfValuesAreNotSet(ResourceRecord $rr, string $exception, string $exceptionMessage): void
    {
        $this->expectException($exception);
        $this->expectExceptionMessage($exceptionMessage);
        $rr->toWire();
    }

    /**
     * @throws Exception
     */
    public function testFromWire(): void
    {
        $encoded = pack(
            'C*',
            0x00,
            0x01,
            0x02,
            0x03,
            0x04,
            0x05,
            0x06,
            0x07, //8-bytes to test the offset
            0x03,
            0x61,
            0x62,
            0x63,
            0x07,
            0x65,
            0x78,
            0x61,
            0x6D,
            0x70,
            0x6C,
            0x65,
            0x03,
            0x63,
            0x6F,
            0x6D,
            0x00, //(3)abc(7)example(3)com(NULL)
            0x00,
            0x01, //A (1)
            0x00,
            0x01, //IN (1)
            0x00,
            0x00,
            0x0E,
            0x10, //3600
            0x00,
            0x04, //4 (RDLENGTH)
            0xC0,
            0xA8,
            0x01,
            0x01 //192.168.1.1
        );

        $offset = 8;
        $a = Factory::A('192.168.1.1');
        $expectation = new ResourceRecord();
        $expectation->setName('abc.example.com.');
        $expectation->setClass(Classes::INTERNET);
        $expectation->setRdata($a);
        $expectation->setTtl(3600);

        $this->assertEquals($expectation, ResourceRecord::fromWire($encoded, $offset));
        $this->assertEquals(39, $offset);
    }
}
