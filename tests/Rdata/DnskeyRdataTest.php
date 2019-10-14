<?php

/*
 * This file is part of Badcow DNS Library.
 *
 * (c) Samuel Williams <sam@badcow.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Badcow\DNS\Tests\Rdata;

use Badcow\DNS\Rdata\Algorithms;
use Badcow\DNS\Rdata\DNSKEY;
use Badcow\DNS\Rdata\Factory;
use PHPUnit\Framework\TestCase;

class DnskeyRdataTest extends TestCase
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
        $dnskey->setPublicKey(self::$publicKey);

        $this->assertEquals($expectation, $dnskey->output());
    }

    public function testFactory(): void
    {
        $dnskey = Factory::Dnskey(256, Algorithms::RSASHA1, self::$publicKey);
        $output = '256 3 5 '.self::$publicKey;

        $this->assertEquals(256, $dnskey->getFlags());
        $this->assertEquals(5, $dnskey->getAlgorithm());
        $this->assertEquals(self::$publicKey, $dnskey->getPublicKey());
        $this->assertEquals(3, $dnskey->getProtocol());
        $this->assertEquals($output, $dnskey->output());
    }
}
