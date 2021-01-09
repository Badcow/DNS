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
use Badcow\DNS\Rdata\NSEC3;
use Base32\Base32Hex;
use PHPUnit\Framework\TestCase;

class Nsec3Test extends TestCase
{
    public function getDataProvider(): array
    {
        return [
            ['1 1 10 12345678 589R358VSPJUFVAJU949JPVF74D9PTGH A RRSIG', true, 10, '12345678', 'ns.sub.delzsk.example.', ['A', 'RRSIG'], '589R358VSPJUFVAJU949JPVF74D9PTGH'],
            ['1 0 10 - JGU2L7C3LKLHAKC5RHUOORTI2DCKK3KL TXT RRSIG', false, 10, '', 'a.test.', ['TXT', 'RRSIG'], 'JGU2L7C3LKLHAKC5RHUOORTI2DCKK3KL'],
        ];
    }

    public function testGetType(): void
    {
        $nsec3 = new NSEC3();
        $this->assertEquals('NSEC3', $nsec3->getType());
    }

    public function testGetTypeCode(): void
    {
        $nsec3 = new NSEC3();
        $this->assertEquals(50, $nsec3->getTypeCode());
    }

    /**
     * @dataProvider getDataProvider
     */
    public function testToText(string $text, bool $unsignedDelegationsCovered, int $iterations, string $salt, string $nextOwnerName, array $types, string $nextHashedOwnerName): void
    {
        $nsec3 = new NSEC3();
        $nsec3->setUnsignedDelegationsCovered($unsignedDelegationsCovered);
        $nsec3->setIterations($iterations);
        $nsec3->setSalt($salt);
        $nsec3->setNextOwnerName($nextOwnerName);
        $nsec3->setTypes($types);
        $nsec3->calculateNextOwnerHash();

        $this->assertEquals($text, $nsec3->toText());
    }

    /**
     * @dataProvider getDataProvider
     */
    public function testWire(string $text, bool $unsignedDelegationsCovered, int $iterations, string $salt, string $nextOwnerName, array $types, string $nextHashedOwnerName): void
    {
        $nsec3 = new NSEC3();
        $nsec3->setUnsignedDelegationsCovered($unsignedDelegationsCovered);
        $nsec3->setIterations($iterations);
        $nsec3->setSalt($salt);
        $nsec3->setNextHashedOwnerName(Base32Hex::decode($nextHashedOwnerName));
        $nsec3->setTypes($types);

        $wireFormat = $nsec3->toWire();

        $fromWire = new NSEC3();
        $fromWire->fromWire($wireFormat);
        $this->assertEquals($nsec3, $fromWire);
    }

    /**
     * @dataProvider getDataProvider
     */
    public function testFromText(string $text, bool $unsignedDelegationsCovered, int $iterations, string $salt, string $nextOwnerName, array $types, string $nextHashedOwnerName): void
    {
        $fromText = new NSEC3();
        $fromText->fromText($text);

        $this->assertEquals($fromText->isUnsignedDelegationsCovered(), $unsignedDelegationsCovered);
        $this->assertEquals($fromText->getIterations(), $iterations);
        $this->assertEquals($fromText->getSalt(), $salt);
        $this->assertEquals($fromText->getTypes(), $types);
        $this->assertEquals($fromText->getNextHashedOwnerName(), Base32Hex::decode($nextHashedOwnerName));
    }

    /**
     * @dataProvider getDataProvider
     */
    public function testFactory(string $text, bool $unsignedDelegationsCovered, int $iterations, string $salt, string $nextOwnerName, array $types, string $nextHashedOwnerName): void
    {
        $nsec3 = Factory::NSEC3($unsignedDelegationsCovered, $iterations, $salt, $nextOwnerName, $types);
        $this->assertEquals($nextHashedOwnerName, Base32Hex::encode($nsec3->getNextHashedOwnerName()));
        $this->assertEquals($text, $nsec3->toText());
    }
}
