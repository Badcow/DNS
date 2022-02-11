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
use Badcow\DNS\Question;
use Badcow\DNS\Rcode;
use Badcow\DNS\Rdata\A;
use Badcow\DNS\Rdata\MX;
use Badcow\DNS\Rdata\NS;
use Badcow\DNS\Rdata\OPT;
use Badcow\DNS\Rdata\UnknownType;
use Badcow\DNS\Rdata\UnsupportedTypeException;
use Badcow\DNS\ResourceRecord;
use Badcow\DNS\UnsetValueException;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    /**
     * @throws \Exception
     */
    private function getWireTestData(int $n): string
    {
        $filename = sprintf(__DIR__.'/Resources/wire/wire_test.data%d', $n);

        if (!file_exists($filename)) {
            throw new \Exception(sprintf('Could not locate resource "%s". FILE NOT FOUND.', $filename));
        }

        if (false === $data = file_get_contents($filename)) {
            throw new \Exception(sprintf('Could not access resource "%s".', $filename));
        }

        $data = preg_replace(['/0x/', '/#.*/', '/(?:\r|\n|\s)*/'], '', $data);

        return hex2bin($data);
    }

    /**
     * @throws UnsupportedTypeException
     * @throws \Exception
     */
    public function testWire1(): void
    {
        $data = $this->getWireTestData(1);

        $msg = Message::fromWire($data);

        $this->assertInstanceOf(Message::class, $msg);

        $this->assertEquals(10, $msg->getId());
        $this->assertEquals(true, $msg->isResponse());
        $this->assertEquals(false, $msg->isQuery());
        $this->assertEquals(Opcode::QUERY, $msg->getOpcode());
        $this->assertEquals(true, $msg->isAuthoritative());
        $this->assertEquals(false, $msg->isTruncated());
        $this->assertEquals(true, $msg->isRecursionDesired());
        $this->assertEquals(true, $msg->isRecursionAvailable());
        $this->assertEquals(0, $msg->getBit9());
        $this->assertEquals(false, $msg->isAuthenticData());
        $this->assertEquals(false, $msg->isCheckingDisabled());
        $this->assertEquals(Rcode::NOERROR, $msg->getRcode());

        $this->assertEquals(1, $msg->countQuestions());
        $this->assertEquals(3, $msg->countAnswers());
        $this->assertEquals(0, $msg->countAuthoritatives());
        $this->assertEquals(3, $msg->countAdditionals());

        $question = $msg->getQuestions()[0];
        $this->assertEquals('vix.com.', $question->getName());
        $this->assertEquals(1, $question->getClassId());
        $this->assertEquals(2, $question->getTypeCode());

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

    /**
     * @throws UnsupportedTypeException
     * @throws \Exception
     */
    public function testWire2(): void
    {
        $data = $this->getWireTestData(2);
        $msg = Message::fromWire($data);

        $this->assertInstanceOf(Message::class, $msg);
    }

    /**
     * @throws UnsupportedTypeException
     * @throws \Exception
     */
    public function testWire3(): void
    {
        $data = $this->getWireTestData(3);
        $msg = Message::fromWire($data);

        $this->assertInstanceOf(Message::class, $msg);
    }

    /**
     * @throws UnsupportedTypeException
     * @throws \Exception
     */
    public function testWire4(): void
    {
        $data = $this->getWireTestData(4);
        $msg = Message::fromWire($data);

        $this->assertInstanceOf(Message::class, $msg);

        $this->assertEquals(6, $msg->getId());
        $this->assertEquals(true, $msg->isResponse());
        $this->assertEquals(Opcode::QUERY, $msg->getOpcode());
        $this->assertEquals(false, $msg->isAuthoritative());
        $this->assertEquals(false, $msg->isTruncated());
        $this->assertEquals(true, $msg->isRecursionDesired());
        $this->assertEquals(true, $msg->isRecursionAvailable());
        $this->assertEquals(0, $msg->getBit9());
        $this->assertEquals(false, $msg->isAuthenticData());
        $this->assertEquals(false, $msg->isCheckingDisabled());
        $this->assertEquals(Rcode::NOERROR, $msg->getRcode());

        $this->assertEquals(1, $msg->countQuestions());
        $this->assertEquals(7, $msg->countAnswers());
        $this->assertEquals(2, $msg->countAuthoritatives());
        $this->assertEquals(17, $msg->countAdditionals());

        $question = $msg->getQuestions()[0];
        $this->assertEquals('aol.com.', $question->getName());
        $this->assertEquals(1, $question->getClassId());
        $this->assertEquals(15, $question->getTypeCode());

        $mx = $msg->getAnswers()[6];
        $this->assertInstanceOf(ResourceRecord::class, $mx);
        $this->assertEquals('aol.com.', $mx->getName());
        $this->assertEquals(3355, $mx->getTtl());
        $this->assertEquals(Classes::INTERNET, $mx->getClass());
        $this->assertInstanceOf(MX::class, $mx->getRdata());
        $this->assertEquals(15, $mx->getRdata()->getPreference());
        $this->assertEquals('zc.mx.aol.com.', $mx->getRdata()->getExchange());

        $ns = $msg->getAuthoritatives()[1];
        $this->assertInstanceOf(ResourceRecord::class, $ns);
        $this->assertEquals('aol.com.', $ns->getName());
        $this->assertEquals(3355, $ns->getTtl());
        $this->assertEquals(Classes::INTERNET, $ns->getClass());
        $this->assertInstanceOf(NS::class, $ns->getRdata());
        $this->assertEquals('DNS-02.NS.aol.com.', $ns->getRdata()->getTarget());

        $a = $msg->getAdditionals()[14];
        $this->assertInstanceOf(ResourceRecord::class, $a);
        $this->assertEquals('yc.mx.aol.com.', $a->getName());
        $this->assertEquals(3356, $a->getTtl());
        $this->assertEquals(Classes::INTERNET, $a->getClass());
        $this->assertInstanceOf(A::class, $a->getRdata());
        $this->assertEquals('205.188.156.130', $a->getRdata()->getAddress());
    }

    /**
     * @throws UnsupportedTypeException
     * @throws UnsetValueException
     * @throws \Exception
     */
    public function testWire5(): void
    {
        $expectation = $this->getWireTestData(5);
        $msg = Message::fromWire($this->getWireTestData(1));

        $this->assertEquals($expectation, $msg->toWire());
    }

    /**
     * @throws UnsetValueException
     * @throws UnsupportedTypeException
     * @throws \Exception
     */
    public function testWire6(): void
    {
        $expectation = $this->getWireTestData(6);
        $msg = Message::fromWire($this->getWireTestData(4));

        $this->assertEquals($expectation, $msg->toWire());
    }

    public function testWire7(): void
    {
        $expectation = $this->getWireTestData(7);
        $msg = Message::fromWire($this->getWireTestData(7));
        $additionals = $msg->getAdditionals();
        $this->assertCount(1, $additionals);
        $this->assertInstanceOf(UnknownType::class, $additionals[0]->getRdata());
    }

    /**
     * @throws UnsetValueException
     * @throws UnsupportedTypeException
     */
    public function testWire8(): void
    {
        $expectation = $this->getWireTestData(8);
        $msg = Message::fromWire($this->getWireTestData(8));
        $additionals = $msg->getAdditionals();
        $this->assertCount(1, $additionals);
        $this->assertInstanceOf(OPT::class, $additionals[0]->getRdata());
        $this->assertEquals($expectation, $msg->toWire());
    }

    /**
     * @throws UnsupportedTypeException
     * @throws \Exception
     */
    public function testSetters(): void
    {
        $msg = Message::fromWire($this->getWireTestData(4));

        $questions = $msg->getQuestions();
        $answers = $msg->getAnswers();
        $additionals = $msg->getAdditionals();
        $authoritatives = $msg->getAuthoritatives();

        $this->assertEquals(1, $msg->countQuestions());
        $this->assertEquals(7, $msg->countAnswers());
        $this->assertEquals(2, $msg->countAuthoritatives());
        $this->assertEquals(17, $msg->countAdditionals());

        $msg->setQuestions([]);
        $msg->setAnswers([]);
        $msg->setAdditionals([]);
        $msg->setAuthoritatives([]);

        $this->assertEquals(0, $msg->countQuestions());
        $this->assertEquals(0, $msg->countAnswers());
        $this->assertEquals(0, $msg->countAuthoritatives());
        $this->assertEquals(0, $msg->countAdditionals());

        $msg->setQuestions($questions);
        $msg->setAnswers($answers);
        $msg->setAdditionals($additionals);
        $msg->setAuthoritatives($authoritatives);

        $this->assertEquals(1, $msg->countQuestions());
        $this->assertEquals(7, $msg->countAnswers());
        $this->assertEquals(2, $msg->countAuthoritatives());
        $this->assertEquals(17, $msg->countAdditionals());
    }

    /**
     * @throws UnsetValueException
     * @throws UnsupportedTypeException
     * @throws \Exception
     */
    public function testMessage0(): void
    {
        $expectation = $this->getWireTestData(0);
        $msg = new Message();
        $msg->setId(42);
        $msg->setQuery(true);

        $question = new Question();
        $question->setName('foo.bar.com.');
        $question->setType('A');
        $question->setClass('IN');

        $msg->addQuestion($question);

        $this->assertEquals($expectation, $msg->toWire());
    }
}
