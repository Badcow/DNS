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
use Badcow\DNS\Rdata\A;
use Badcow\DNS\Rdata\Factory;
use PHPUnit\Framework\TestCase;

class SigTest extends TestCase
{
    public function testFactory(): void
    {
        $signature = base64_decode('oJB1W6WNGv+ldvQ3WDG0MQkg5IEhjRip8WTrPYGv07h108dUKGMeDPKijVCHX3DDKdfb+v6oB9wfuh3DTJXUA'.
            'fI/M0zmO/zz8bW0Rznl8O3tGNazPwQKkRN20XPXV6nwwfoXmJQbsLNrLfkGJ5D6fwFm8nN+6pBzeDQfsS3Ap3o=');

        $sig = Factory::SIG(
            A::TYPE,
            Algorithms::RSASHA1,
            3,
            86400,
            \DateTime::createFromFormat('Ymd H:i:s', '20220101 00:00:00'),
            \DateTime::createFromFormat('Ymd H:i:s', '20180101 00:00:00'),
            2642,
            'example.com.',
            $signature
        );

        $this->assertEquals(A::TYPE, $sig->getTypeCovered());
        $this->assertEquals(Algorithms::RSASHA1, $sig->getAlgorithm());
        $this->assertEquals(3, $sig->getLabels());
        $this->assertEquals(86400, $sig->getOriginalTtl());
        $this->assertEquals(\DateTime::createFromFormat('Ymd H:i:s', '20220101 00:00:00'), $sig->getSignatureExpiration());
        $this->assertEquals(\DateTime::createFromFormat('Ymd H:i:s', '20180101 00:00:00'), $sig->getSignatureInception());
        $this->assertEquals(2642, $sig->getKeyTag());
        $this->assertEquals('example.com.', $sig->getSignersName());
        $this->assertEquals($signature, $sig->getSignature());
    }
}
