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
use Badcow\DNS\Rdata\RRSIG;
use Badcow\DNS\Rdata\UnsupportedTypeException;
use PHPUnit\Framework\TestCase;

class RrsigTest extends TestCase
{
    private static $signature = 'oJB1W6WNGv+ldvQ3WDG0MQkg5IEhjRip8WTrPYGv07h108dUKGMeDPKijVCHX3DDKdfb+v6oB9wfuh3DTJXUA'.
        'fI/M0zmO/zz8bW0Rznl8O3tGNazPwQKkRN20XPXV6nwwfoXmJQbsLNrLfkGJ5D6fwFm8nN+6pBzeDQfsS3Ap3o=';

    public function testToText(): void
    {
        if (2147483647 === PHP_INT_MAX) {
            $this->markTestSkipped('RRSIG test does not work on 32-bit systems.');
        }

        $expectation = 'A 5 3 86400 20050322173103 20030220173103 2642 example.com. '.self::$signature;

        $rrsig = new RRSIG();

        $rrsig->setTypeCovered('A');
        $rrsig->setAlgorithm(Algorithms::RSASHA1);
        $rrsig->setLabels(3);
        $rrsig->setOriginalTtl(86400);
        $rrsig->setSignatureExpiration(\DateTime::createFromFormat(RRSIG::TIME_FORMAT, '20050322173103'));
        $rrsig->setSignatureInception(\DateTime::createFromFormat(RRSIG::TIME_FORMAT, '20030220173103'));
        $rrsig->setKeyTag(2642);
        $rrsig->setSignersName('example.com.');
        $rrsig->setSignature(self::$signature);

        $this->assertEquals($expectation, $rrsig->toText());
    }

    public function testFactory(): void
    {
        $rrsig = Factory::RRSIG(
            A::TYPE,
            Algorithms::RSASHA1,
            3,
            86400,
            \DateTime::createFromFormat('Ymd', '20220101'),
            \DateTime::createFromFormat('Ymd', '20180101'),
            2642,
            'example.com.',
            self::$signature
        );

        $this->assertEquals(A::TYPE, $rrsig->getTypeCovered());
        $this->assertEquals(Algorithms::RSASHA1, $rrsig->getAlgorithm());
        $this->assertEquals(3, $rrsig->getLabels());
        $this->assertEquals(86400, $rrsig->getOriginalTtl());
        $this->assertEquals(\DateTime::createFromFormat('Ymd', '20220101'), $rrsig->getSignatureExpiration());
        $this->assertEquals(\DateTime::createFromFormat('Ymd', '20180101'), $rrsig->getSignatureInception());
        $this->assertEquals(2642, $rrsig->getKeyTag());
        $this->assertEquals('example.com.', $rrsig->getSignersName());
        $this->assertEquals(self::$signature, $rrsig->getSignature());
    }

    public function testFromText(): void
    {
        $text = 'A 5 3 86400 20050322173103 20030220173103 2642 example.com. '.self::$signature;

        $rrsig = new RRSIG();
        $rrsig->setTypeCovered('A');
        $rrsig->setAlgorithm(Algorithms::RSASHA1);
        $rrsig->setLabels(3);
        $rrsig->setOriginalTtl(86400);
        $rrsig->setSignatureExpiration(\DateTime::createFromFormat(RRSIG::TIME_FORMAT, '20050322173103'));
        $rrsig->setSignatureInception(\DateTime::createFromFormat(RRSIG::TIME_FORMAT, '20030220173103'));
        $rrsig->setKeyTag(2642);
        $rrsig->setSignersName('example.com.');
        $rrsig->setSignature(self::$signature);

        $this->assertEquals($rrsig, RRSIG::fromText($text));
    }

    /**
     * @throws UnsupportedTypeException
     */
    public function testWire(): void
    {
        $rrsig = new RRSIG();
        $rrsig->setTypeCovered('A');
        $rrsig->setAlgorithm(Algorithms::RSASHA1);
        $rrsig->setLabels(3);
        $rrsig->setOriginalTtl(86400);
        $rrsig->setSignatureExpiration(\DateTime::createFromFormat(RRSIG::TIME_FORMAT, '20050322173103'));
        $rrsig->setSignatureInception(\DateTime::createFromFormat(RRSIG::TIME_FORMAT, '20030220173103'));
        $rrsig->setKeyTag(2642);
        $rrsig->setSignersName('example.com.');
        $rrsig->setSignature(self::$signature);

        $wireFormat = $rrsig->toWire();
        $rdLength = strlen($wireFormat);
        $wireFormat = 'abcd'.$wireFormat;
        $offset = 4;

        $this->assertEquals($rrsig, RRSIG::fromWire($wireFormat, $offset, $rdLength));
        $this->assertEquals(4 + $rdLength, $offset);
    }
}
