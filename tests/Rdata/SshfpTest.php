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

use Badcow\DNS\Rdata\Factory;
use Badcow\DNS\Rdata\SSHFP;
use PHPUnit\Framework\TestCase;

class SshfpTest extends TestCase
{
    public function dataProvider_testExceptions(): array
    {
        return [
            //[Algorithm, FPType, Fingerprint, ExpectedException, ExpectedExceptionMessage]
            [-1, 255, '123456789abcdef67890123456789abcdef67890', \InvalidArgumentException::class, 'Algorithm must be an 8-bit integer between 0 and 255.'],
            [256, 255, '123456789abcdef67890123456789abcdef67890', \InvalidArgumentException::class, 'Algorithm must be an 8-bit integer between 0 and 255.'],
            [0, -1, '123456789abcdef67890123456789abcdef67890', \InvalidArgumentException::class, 'Fingerprint type must be an 8-bit integer between 0 and 255.'],
            [0, 256, '123456789abcdef67890123456789abcdef67890', \InvalidArgumentException::class, 'Fingerprint type must be an 8-bit integer between 0 and 255.'],
        ];
    }

    public function testOutput(): void
    {
        $sshfp = new SSHFP();
        $sshfp->setAlgorithm(SSHFP::ALGORITHM_DSA);
        $sshfp->setFingerprintType(SSHFP::FP_TYPE_SHA1);
        $sshfp->setFingerprint(hex2bin('123456789abcdef67890123456789abcdef67890'));

        $expectation = '2 1 123456789abcdef67890123456789abcdef67890';
        $this->assertEquals($expectation, $sshfp->toText());
    }

    /**
     * @dataProvider dataProvider_testExceptions
     */
    public function testExceptions(int $algorithm, int $fpType, string $fingerprint, string $expectedException, string $expectedExceptionMessage): void
    {
        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedExceptionMessage);

        Factory::SSHFP($algorithm, $fpType, $fingerprint);
    }

    public function testGetType(): void
    {
        $sshfp = new SSHFP();
        $this->assertEquals('SSHFP', $sshfp->getType());
    }

    public function testGetTypeCode(): void
    {
        $sshfp = new SSHFP();
        $this->assertEquals(44, $sshfp->getTypeCode());
    }

    public function testToText(): void
    {
        $expectation = '2 1 123456789abcdef67890123456789abcdef67890';
        $sshfp = new SSHFP();
        $sshfp->setAlgorithm(2);
        $sshfp->setFingerprintType(1);
        $sshfp->setFingerprint(hex2bin('123456789abcdef67890123456789abcdef67890'));

        $this->assertEquals($expectation, $sshfp->toText());
    }

    public function testWire(): void
    {
        $wireFormat = chr(2).chr(1).hex2bin('123456789abcdef67890123456789abcdef67890');
        $sshfp = new SSHFP();
        $sshfp->setAlgorithm(2);
        $sshfp->setFingerprintType(1);
        $sshfp->setFingerprint(hex2bin('123456789abcdef67890123456789abcdef67890'));

        $this->assertEquals($wireFormat, $sshfp->toWire());

        $wireFormat = 'zyxwvut'.$wireFormat;
        $offset = 7;
        $fromWire = new SSHFP();
        $fromWire->fromWire($wireFormat, $offset, 40);
        $this->assertEquals($sshfp, $fromWire);
    }

    public function testFromText(): void
    {
        $expectation = new SSHFP();
        $expectation->setAlgorithm(2);
        $expectation->setFingerprintType(1);
        $expectation->setFingerprint(hex2bin('123456789abcdef67890123456789abcdef67890'));

        $fromText = new SSHFP();
        $fromText->fromText('2 1 123456789abcdef67890123456789abcdef67890');
        $this->assertEquals($expectation, $fromText);
    }

    public function testFactory(): void
    {
        $sshfp = Factory::SSHFP(2, 1, '123456789abcdef67890123456789abcdef67890');

        $this->assertEquals(2, $sshfp->getAlgorithm());
        $this->assertEquals(1, $sshfp->getFingerprintType());
        $this->assertEquals('123456789abcdef67890123456789abcdef67890', $sshfp->getFingerprint());
    }
}
