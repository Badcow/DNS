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
use Badcow\DNS\Rdata\Factory;
use Badcow\DNS\Rdata\KEY;
use PHPUnit\Framework\TestCase;

class KeyTest extends TestCase
{
    /**
     * @var string
     */
    private static $publicKey = 'AQPSKmynfzW4kyBv015MUG2DeIQ3Cbl+BBZH4b/0PY1kxkmvHjcZc8nokfzj31GajIQKY+5CptLr3buXA10hWqTkF7H6RfoRqXQeogmMHfpftf6zMv1LyBUgia7za6ZEzOJBOztyvhjL742iU/TpPSEDhm2SNKLijfUppn1UaNvv4w==';

    public function testOutput(): void
    {
        $expectation = '256 3 5 '.self::$publicKey;

        $key = new KEY();
        $key->setFlags(256);
        $key->setProtocol(3);
        $key->setAlgorithm(Algorithms::RSASHA1);
        $key->setPublicKey(base64_decode(self::$publicKey));

        $this->assertEquals($expectation, $key->toText());
    }

    public function testFactory(): void
    {
        $key = Factory::KEY(256, 3, Algorithms::RSASHA1, base64_decode(self::$publicKey));
        $output = '256 3 5 '.self::$publicKey;

        $this->assertEquals(256, $key->getFlags());
        $this->assertEquals(5, $key->getAlgorithm());
        $this->assertEquals(base64_decode(self::$publicKey), $key->getPublicKey());
        $this->assertEquals(3, $key->getProtocol());
        $this->assertEquals($output, $key->toText());
    }

    public function testFromText(): void
    {
        $rdata = '256 3 5 AQPSKmynfzW4kyBv015MUG2DeIQ3 Cbl+BBZH4b/0PY1kxkmvHjcZc8no kfzj31GajIQKY+5CptLr3buXA10h WqTkF7H6RfoRqXQeogmMHfpftf6z Mv1LyBUgia7za6ZEzOJBOztyvhjL 742iU/TpPSEDhm2SNKLijfUppn1U aNvv4w==';
        $key = new KEY();
        $key->setFlags(256);
        $key->setProtocol(3);
        $key->setAlgorithm(Algorithms::RSASHA1);
        $key->setPublicKey(base64_decode(self::$publicKey));

        $fromText = new KEY();
        $fromText->fromText($rdata);
        $this->assertEquals($key, $fromText);
    }

    public function testWire(): void
    {
        $wireFormat = pack('nCC', 256, 3, 5).base64_decode(self::$publicKey);

        $key = new KEY();
        $key->setFlags(256);
        $key->setProtocol(3);
        $key->setAlgorithm(Algorithms::RSASHA1);
        $key->setPublicKey(base64_decode("AQPSKmynfzW4kyBv015MUG2DeIQ3Cbl+BBZH4b/\r\n0PY1kxkmvHjcZc8nokfzj31GajIQKY+5CptLr3buXA10hWqTkF7H6RfoRqXQe   ogmMHfpftf6zMv1LyBUgia7za6ZEzOJBOztyvhjL742iU\n/TpPSEDhm2SNKLijfUppn1UaNvv4w=="));

        $this->assertEquals($wireFormat, $key->toWire());

        $rdLength = strlen($wireFormat);
        $wireFormat = 'abcde'.$wireFormat.'fghijk';
        $offset = 5;

        $fromWire = new KEY();
        $fromWire->fromWire($wireFormat, $offset, $rdLength);
        $this->assertEquals($key, $fromWire);
        $this->assertEquals(5 + $rdLength, $offset);
    }
}
