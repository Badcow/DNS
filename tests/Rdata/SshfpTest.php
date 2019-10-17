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
            [1, 1, '0x123456789abcdef67890023456789abcdef67890', \InvalidArgumentException::class, 'The fingerprint MUST be a hexadecimal value.'],
        ];
    }

    public function testOutput(): void
    {
        $sshfp = new SSHFP();
        $sshfp->setAlgorithm(SSHFP::ALGORITHM_DSA);
        $sshfp->setFingerprintType(SSHFP::FP_TYPE_SHA1);
        $sshfp->setFingerprint('123456789abcdef67890123456789abcdef67890');

        $expectation = '2 1 123456789abcdef67890123456789abcdef67890';
        $this->assertEquals($expectation, $sshfp->output());
    }

    /**
     * @dataProvider dataProvider_testExceptions
     *
     * @param int    $algorithm
     * @param int    $fpType
     * @param string $fingerprint
     * @param string $expectedException
     * @param string $expectedExceptionMessage
     */
    public function testExceptions(int $algorithm, int $fpType, string $fingerprint, string $expectedException, string $expectedExceptionMessage): void
    {
        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedExceptionMessage);

        Factory::SSHFP($algorithm, $fpType, $fingerprint);
    }
}
