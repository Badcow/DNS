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

use Badcow\DNS\Algorithms;
use Badcow\DNS\Rdata\DNSKEY;
use Badcow\DNS\Rdata\Factory;
use PHPUnit\Framework\TestCase;

class DnskeyTest extends TestCase
{
    /**
     * @var string
     */
    private static $publicKey = 'AQPSKmynfzW4kyBv015MUG2DeIQ3Cbl+BBZH4b/0PY1kxkmvHjcZc8nokfzj31GajIQKY+5CptLr3buXA10hWqTkF7H6RfoRqXQeogmMHfpftf6zMv1LyBUgia7za6ZEzOJBOztyvhjL742iU/TpPSEDhm2SNKLijfUppn1UaNvv4w==';

    public function testOutput(): void
    {
        $expectation = '256 3 5 '.self::$publicKey;

        $dnskey = new DNSKEY();
        $dnskey->setFlags(256);
        $dnskey->setAlgorithm(Algorithms::RSASHA1);
        $dnskey->setPublicKey(base64_decode(self::$publicKey));

        $this->assertEquals($expectation, $dnskey->toText());
    }

    public function testSetProtocolThrowsException(): void
    {
        $dnskey = new DNSKEY();
        $this->expectException(\InvalidArgumentException::class);
        $dnskey->setProtocol(2);
    }

    public function testFactory(): void
    {
        $dnskey = Factory::DNSKEY(256, Algorithms::RSASHA1, base64_decode(self::$publicKey));
        $output = '256 3 5 '.self::$publicKey;

        $this->assertEquals(256, $dnskey->getFlags());
        $this->assertEquals(5, $dnskey->getAlgorithm());
        $this->assertEquals(base64_decode(self::$publicKey), $dnskey->getPublicKey());
        $this->assertEquals(3, $dnskey->getProtocol());
        $this->assertEquals($output, $dnskey->toText());
    }

    public function testFromText(): void
    {
        $rdata = '256 3 5 AQPSKmynfzW4kyBv015MUG2DeIQ3 Cbl+BBZH4b/0PY1kxkmvHjcZc8no kfzj31GajIQKY+5CptLr3buXA10h WqTkF7H6RfoRqXQeogmMHfpftf6z Mv1LyBUgia7za6ZEzOJBOztyvhjL 742iU/TpPSEDhm2SNKLijfUppn1U aNvv4w==';
        $dnskey = new DNSKEY();
        $dnskey->setFlags(256);
        $dnskey->setProtocol(3);
        $dnskey->setAlgorithm(Algorithms::RSASHA1);
        $dnskey->setPublicKey(base64_decode(self::$publicKey));

        $fromText = new DNSKEY();
        $fromText->fromText($rdata);
        $this->assertEquals($dnskey, $fromText);
    }

    public function testWire(): void
    {
        $wireFormat = pack('nCC', 256, 3, 5).base64_decode(self::$publicKey);

        $dnskey = new DNSKEY();
        $dnskey->setFlags(256);
        $dnskey->setAlgorithm(Algorithms::RSASHA1);
        $dnskey->setPublicKey(base64_decode("AQPSKmynfzW4kyBv015MUG2DeIQ3Cbl+BBZH4b/\r\n0PY1kxkmvHjcZc8nokfzj31GajIQKY+5CptLr3buXA10hWqTkF7H6RfoRqXQe   ogmMHfpftf6zMv1LyBUgia7za6ZEzOJBOztyvhjL742iU\n/TpPSEDhm2SNKLijfUppn1UaNvv4w=="));

        $this->assertEquals($wireFormat, $dnskey->toWire());

        $rdLength = strlen($wireFormat);
        $wireFormat = 'abcde'.$wireFormat.'fghijk';
        $offset = 5;

        $fromWire = new DNSKEY();
        $fromWire->fromWire($wireFormat, $offset, $rdLength);

        $this->assertEquals($dnskey, $fromWire);
        $this->assertEquals(5 + $rdLength, $offset);
    }
}
