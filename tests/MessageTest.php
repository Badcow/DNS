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
use Badcow\DNS\Message;
use Badcow\DNS\Opcode;
use Badcow\DNS\Rcode;
use Badcow\DNS\Rdata\A;
use Badcow\DNS\Rdata\NS;
use Badcow\DNS\ResourceRecord;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    private function getWireTestData(int $n): string
    {
        $filename = sprintf(__DIR__.'/Resources/wire/wire_test.data%d', $n);
        $data = preg_replace(['/0x/', '/\#.*/', '/(?:\r|\n|\s)*/'], '', file_get_contents($filename));

        return hex2bin($data);
    }

    public function testWire1(): void
    {
        $data = $this->getWireTestData(1);

        $msg = Message::fromWire($data);

        $this->assertInstanceOf(Message::class, $msg);

        $this->assertEquals(10, $msg->getId());
        $this->assertEquals(true, $msg->isResponse());
        $this->assertEquals(Opcode::QUERY, $msg->getOpcode());
        $this->assertEquals(true, $msg->isAuthoritative());
        $this->assertEquals(false, $msg->isTruncated());
        $this->assertEquals(true, $msg->isRecursionDesired());
        $this->assertEquals(true, $msg->isRecursionAvailable());
        $this->assertEquals(0, $msg->getZ());
        $this->assertEquals(Rcode::NOERROR, $msg->getRcode());

        $this->assertEquals(1, $msg->countQuestions());
        $this->assertEquals(3, $msg->countAnswers());
        $this->assertEquals(0, $msg->countAuthoritatives());
        $this->assertEquals(3, $msg->countAdditionals());

        $question = $msg->getQuestions()[0];
        $this->assertEquals('vix.com.', $question->getName());
        $this->assertEquals(1, $question->getClass());
        $this->assertEquals(2, $question->getType());

        $ns1 = $msg->getAnswers()[0];
        $this->assertInstanceOf(ResourceRecord::class, $ns1);
        $this->assertEquals('vix.com.', $ns1->getName());
        $this->assertEquals(3600, $ns1->getTtl());
        $this->assertEquals(Classes::INTERNET, $ns1->getClass());
        $this->assertInstanceOf(NS::class, $ns1->getRdata());
        $this->assertEquals('isrv1.pa.vix.com.', $ns1->getRdata()->getTarget());

        $ns2 = $msg->getAnswers()[1];
        $this->assertInstanceOf(ResourceRecord::class, $ns2);
        $this->assertEquals('vix.com.', $ns2->getName());
        $this->assertEquals(3600, $ns2->getTtl());
        $this->assertEquals(Classes::INTERNET, $ns2->getClass());
        $this->assertInstanceOf(NS::class, $ns2->getRdata());
        $this->assertEquals('ns-ext.vix.com.', $ns2->getRdata()->getTarget());

        $ns3 = $msg->getAnswers()[2];
        $this->assertInstanceOf(ResourceRecord::class, $ns3);
        $this->assertEquals('vix.com.', $ns3->getName());
        $this->assertEquals(3600, $ns3->getTtl());
        $this->assertEquals(Classes::INTERNET, $ns3->getClass());
        $this->assertInstanceOf(NS::class, $ns3->getRdata());
        $this->assertEquals('ns1.gnac.com.', $ns3->getRdata()->getTarget());

        $a1 = $msg->getAdditionals()[0];
        $this->assertInstanceOf(ResourceRecord::class, $a1);
        $this->assertEquals('isrv1.pa.vix.com.', $a1->getName());
        $this->assertEquals(3600, $a1->getTtl());
        $this->assertEquals(Classes::INTERNET, $a1->getClass());
        $this->assertInstanceOf(A::class, $a1->getRdata());
        $this->assertEquals('204.152.184.134', $a1->getRdata()->getAddress());

        $a2 = $msg->getAdditionals()[1];
        $this->assertInstanceOf(ResourceRecord::class, $a2);
        $this->assertEquals('ns-ext.vix.com.', $a2->getName());
        $this->assertEquals(3600, $a2->getTtl());
        $this->assertEquals(Classes::INTERNET, $a2->getClass());
        $this->assertInstanceOf(A::class, $a2->getRdata());
        $this->assertEquals('204.152.184.64', $a2->getRdata()->getAddress());

        $a3 = $msg->getAdditionals()[2];
        $this->assertInstanceOf(ResourceRecord::class, $a3);
        $this->assertEquals('ns1.gnac.com.', $a3->getName());
        $this->assertEquals(172362, $a3->getTtl());
        $this->assertEquals(Classes::INTERNET, $a3->getClass());
        $this->assertInstanceOf(A::class, $a3->getRdata());
        $this->assertEquals('198.151.248.246', $a3->getRdata()->getAddress());
    }

    public function testWire2(): void
    {
        $data = $this->getWireTestData(2);
        $msg = Message::fromWire($data);

        $this->assertInstanceOf(Message::class, $msg);
    }

    public function testWire3(): void
    {
        $data = $this->getWireTestData(3);
        $msg = Message::fromWire($data);

        $this->assertInstanceOf(Message::class, $msg);
    }

    public function testWire4(): void
    {
        $expectation = $this->getWireTestData(5);
        $msg = Message::fromWire($this->getWireTestData(1));

        $this->assertEquals($expectation, $msg->toWire());
    }
}
