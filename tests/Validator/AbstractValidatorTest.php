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

namespace Badcow\DNS\Tests;

use Badcow\DNS\Rdata\Factory;
use Badcow\DNS\ResourceRecord;
use Badcow\DNS\Validator\AbstractValidator;
use PHPUnit\Framework\TestCase;

class AbstractValidatorTest extends TestCase
{
    public function testNoCNAMEinZone(): void
    {
        $zone = TestZone::buildTestZone();

        $invalidCname = new ResourceRecord();
        $invalidCname->setName('alias');
        $invalidCname->setRdata(Factory::CNAME('subdomain.au.example.com.'));
        $this->assertFalse(AbstractValidator::noCNAMEinZone($zone, $invalidCname));

        $validCname = new ResourceRecord();
        $validCname->setName('alias2');
        $validCname->setRdata(Factory::CNAME('subdomain.au.example.com.'));
        $this->assertTrue(AbstractValidator::noCNAMEinZone($zone, $validCname));
    }

    public function testNoDuplicate(): void
    {
        $zone = TestZone::buildTestZone();

        $invalidResourceRecord = new ResourceRecord();
        $invalidResourceRecord->setName('subdomain.au');
        $invalidResourceRecord->setRdata(Factory::A('192.168.1.2'));
        $this->assertFalse(AbstractValidator::noDuplicate($zone, $invalidResourceRecord));

        $validResourceRecord = new ResourceRecord();
        $validResourceRecord->setName('subdomain2.au');
        $validResourceRecord->setRdata(Factory::A('192.168.1.3'));
        $this->assertTrue(AbstractValidator::noDuplicate($zone, $validResourceRecord));
    }

    public function testNameDoesntExists(): void
    {
        $zone = TestZone::buildTestZone();

        $invalidResourceRecord = new ResourceRecord();
        $invalidResourceRecord->setName('subdomain.au');
        $invalidResourceRecord->setRdata(Factory::A('192.168.1.2'));
        $this->assertFalse(AbstractValidator::nameDoesntExists($zone, $invalidResourceRecord));

        $validResourceRecord = new ResourceRecord();
        $validResourceRecord->setName('subdomain2.au');
        $validResourceRecord->setRdata(Factory::A('192.168.1.3'));
        $this->assertTrue(AbstractValidator::nameDoesntExists($zone, $validResourceRecord));
    }
}
