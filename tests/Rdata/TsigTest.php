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

use Badcow\DNS\Parser\ParseException;
use Badcow\DNS\Rcode;
use Badcow\DNS\Rdata\DecodeException;
use Badcow\DNS\Rdata\Factory;
use Badcow\DNS\Rdata\TSIG;
use PHPUnit\Framework\TestCase;

class TsigTest extends TestCase
{
    private $sampleMac = <<<TXT
VGhpcyBwcm90b2NvbCBhbGxvd3MgZm9yIHRyYW5zYWN0aW9uIGxldmVsIGF1dGhlbnRpY2F0aW9uIHV
zaW5nIHNoYXJlZCBzZWNyZXRzIGFuZCBvbmUgd2F5IGhhc2hpbmcuICBJdCBjYW4gYmUgdXNlZCB0by
BhdXRoZW50aWNhdGUgZHluYW1pYyB1cGRhdGVzIGFzIGNvbWluZyBmcm9tIGFuIGFwcHJvdmVkIGNsa
WVudCwgb3IgdG8gYXV0aGVudGljYXRlIHJlc3BvbnNlcyBhcyBjb21pbmcgZnJvbSBhbiBhcHByb3Zl
ZCByZWN1cnNpdmUgbmFtZSBzZXJ2ZXIu
TXT;

    private $sampleOtherData = <<<TXT
Tm8gcHJvdmlzaW9uIGhhcyBiZWVuIG1hZGUgaGVyZSBmb3IgZGlzdHJpYnV0aW5nIHRoZSBzaGFyZWQ
gc2VjcmV0czsKICAgaXQgaXMgZXhwZWN0ZWQgdGhhdCBhIG5ldHdvcmsgYWRtaW5pc3RyYXRvciB3aW
xsIHN0YXRpY2FsbHkgY29uZmlndXJlCiAgIG5hbWUgc2VydmVycyBhbmQgY2xpZW50cyB1c2luZyBzb
21lIG91dCBvZiBiYW5kIG1lY2hhbmlzbSBzdWNoIGFzCiAgIHNuZWFrZXItbmV0IHVudGlsIGEgc2Vj
dXJlIGF1dG9tYXRlZCBtZWNoYW5pc20gZm9yIGtleSBkaXN0cmlidXRpb24KICAgaXMgYXZhaWxhYmx
lLg==
TXT;

    public function testGetType(): void
    {
        $tsig = new TSIG();
        $this->assertEquals('TSIG', $tsig->getType());
    }

    public function testGetTypeCode(): void
    {
        $tsig = new TSIG();
        $this->assertEquals(250, $tsig->getTypeCode());
    }

    public function testToText(): void
    {
        $tsig = new TSIG();
        $tsig->setAlgorithmName('SAMPLE-ALG.EXAMPLE.');
        $tsig->setTimeSigned(new \DateTime('Tue Jan 21 00:00:00 1997'));
        $tsig->setFudge(300);
        $tsig->setMac(base64_decode($this->sampleMac));
        $tsig->setOriginalId(54321);
        $tsig->setError(Rcode::BADALG);
        $tsig->setOtherData(base64_decode($this->sampleOtherData));

        $mac = str_replace(["\r", "\n"], '', $this->sampleMac);
        $otherData = str_replace(["\r", "\n"], '', $this->sampleOtherData);
        $expectation = 'SAMPLE-ALG.EXAMPLE. 853804800 300 '.$mac.' 54321 21 '.$otherData;

        $this->assertEquals($expectation, $tsig->toText());
    }

    /**
     * @throws ParseException
     */
    public function testFromText(): void
    {
        $mac = str_replace(["\r", "\n"], '', $this->sampleMac);
        $otherData = str_replace(["\r", "\n"], '', $this->sampleOtherData);
        $text = 'SAMPLE-ALG.EXAMPLE. 853804800 300 '.$mac.' 54321 21 '.$otherData;
        $expectedTimeSigned = new \DateTime('Tue Jan 21 00:00:00 1997');

        $tsig = new TSIG();
        $tsig->fromText($text);

        $this->assertEquals('SAMPLE-ALG.EXAMPLE.', $tsig->getAlgorithmName());
        $this->assertEquals($expectedTimeSigned, $tsig->getTimeSigned());
        $this->assertEquals(300, $tsig->getFudge());
        $this->assertEquals(base64_decode($mac), $tsig->getMac());
        $this->assertEquals(54321, $tsig->getOriginalId());
        $this->assertEquals(Rcode::BADALG, $tsig->getError());
        $this->assertEquals(base64_decode($otherData), $tsig->getOtherData());
    }

    /**
     * @throws DecodeException
     */
    public function testWire(): void
    {
        $tsig = new TSIG();
        $tsig->setAlgorithmName('SAMPLE-ALG.EXAMPLE.');
        $tsig->setTimeSigned(new \DateTime('Tue Jan 21 00:00:00 1997'));
        $tsig->setFudge(300);
        $tsig->setMac(base64_decode($this->sampleMac));
        $tsig->setOriginalId(54321);
        $tsig->setError(Rcode::BADALG);
        $tsig->setOtherData(base64_decode($this->sampleOtherData));

        $wireFormat = $tsig->toWire();
        $rdLength = strlen($wireFormat);
        $wireFormat = 'abc'.$wireFormat;
        $offset = 3;

        $fromWire = new TSIG();
        $fromWire->fromWire($wireFormat, $offset, $rdLength);
        $this->assertEquals($tsig, $fromWire);
        $this->assertEquals(3 + $rdLength, $offset);
    }

    public function testFactory(): void
    {
        $timeSigned = new \DateTime('Tue Jan 21 00:00:00 1997');
        $tsig = Factory::TSIG(
            'SAMPLE-ALG.EXAMPLE.',
            $timeSigned,
            300,
            base64_decode($this->sampleMac),
            54321,
            Rcode::BADALG,
            base64_decode($this->sampleOtherData)
        );

        $this->assertEquals('SAMPLE-ALG.EXAMPLE.', $tsig->getAlgorithmName());
        $this->assertEquals($timeSigned, $tsig->getTimeSigned());
        $this->assertEquals(300, $tsig->getFudge());
        $this->assertEquals(base64_decode($this->sampleMac), $tsig->getMac());
        $this->assertEquals(54321, $tsig->getOriginalId());
        $this->assertEquals(Rcode::BADALG, $tsig->getError());
        $this->assertEquals(base64_decode($this->sampleOtherData), $tsig->getOtherData());
    }
}
