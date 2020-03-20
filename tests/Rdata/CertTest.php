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
use Badcow\DNS\Rdata\CERT;
use Badcow\DNS\Rdata\Factory;
use PHPUnit\Framework\TestCase;

class CertTest extends TestCase
{
    /**
     * @var string
     */
    private $certificate;

    public function setUp(): void
    {
        $certificate = file_get_contents(__DIR__.'/../Resources/google.com.cer');
        $this->certificate = str_replace(['-----BEGIN CERTIFICATE-----', '-----END CERTIFICATE-----', "\r", "\n"], '', $certificate);
    }

    public function testGetType(): void
    {
        $cert = new CERT();
        $this->assertEquals('CERT', $cert->getType());
    }

    public function testGetTypeCode(): void
    {
        $cert = new CERT();
        $this->assertEquals(37, $cert->getTypeCode());
    }

    public function testToText(): void
    {
        $cert = new CERT();
        $cert->setCertificateType('PGP');
        $cert->setKeyTag(65);
        $cert->setAlgorithm(Algorithms::ECC);
        $cert->setCertificate($this->certificate);

        $expectation = 'PGP 65 ECC '.$this->certificate;

        $this->assertEquals($expectation, $cert->toText());
    }

    public function testWire(): void
    {
        $cert = new CERT();
        $cert->setCertificateType('PGP');
        $cert->setKeyTag(65);
        $cert->setAlgorithm(Algorithms::ECC);
        $cert->setCertificate($this->certificate);

        $wireFormatted = $cert->toWire();

        $this->assertEquals($cert, CERT::fromWire($wireFormatted));
    }

    public function testFromText(): void
    {
        $cert = new CERT();
        $cert->setCertificateType('PGP');
        $cert->setKeyTag(65);
        $cert->setAlgorithm(Algorithms::ECC);
        $cert->setCertificate($this->certificate);

        $text = 'PGP 65 ECC '.$this->certificate;

        $this->assertEquals($cert, CERT::fromText($text));
    }

    public function testFactory(): void
    {
        $cert = new CERT();
        $cert->setCertificateType('PGP');
        $cert->setKeyTag(65);
        $cert->setAlgorithm(Algorithms::ECC);
        $cert->setCertificate($this->certificate);

        $this->assertEquals($cert, Factory::CERT('PGP', 65, Algorithms::ECC, $this->certificate));
    }
}
