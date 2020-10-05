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
use Badcow\DNS\Rdata\CDNSKEY;
use Badcow\DNS\Rdata\Factory;
use PHPUnit\Framework\TestCase;

class CdnskeyTest extends TestCase
{
    /**
     * @var string
     */
    private static $publicKey = 'AQPSKmynfzW4kyBv015MUG2DeIQ3Cbl+BBZH4b/0PY1kxkmvHjcZc8nokfzj31GajIQKY+5CptLr3buXA10hWqTkF7H6RfoRqXQeogmMHfpftf6zMv1LyBUgia7za6ZEzOJBOztyvhjL742iU/TpPSEDhm2SNKLijfUppn1UaNvv4w==';

    public function testFactory(): void
    {
        $cdnskey = Factory::CDNSKEY(256, Algorithms::RSASHA1, base64_decode(self::$publicKey));
        $output = '256 3 5 '.self::$publicKey;

        $this->assertInstanceOf(CDNSKEY::class, $cdnskey);
        $this->assertEquals(256, $cdnskey->getFlags());
        $this->assertEquals(5, $cdnskey->getAlgorithm());
        $this->assertEquals(base64_decode(self::$publicKey), $cdnskey->getPublicKey());
        $this->assertEquals(3, $cdnskey->getProtocol());
        $this->assertEquals($output, $cdnskey->toText());
    }
}
