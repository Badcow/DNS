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
use PHPUnit\Framework\TestCase;

class Nsec3Test extends TestCase
{
    public function getDataProvider(): array
    {
        return [
            ['1 1 10 12345678 589r358vspjufvaju949jpvf74d9ptgh A RRSIG', true, 10, '12345678', 'ns.sub.delzsk.example.', ['A', 'RRSIG'], '589r358vspjufvaju949jpvf74d9ptgh'],
            ['1 0 10 - jgu2l7c3lklhakc5rhuoorti2dckk3kl TXT RRSIG', false, 10, '', 'a.test.', ['TXT', 'RRSIG'], 'jgu2l7c3lklhakc5rhuoorti2dckk3kl'],
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
        $nsec3->setNextHashedOwnerName(NSEC3::base32decode($nextHashedOwnerName));
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
        $this->assertEquals($fromText->getNextHashedOwnerName(), NSEC3::base32decode($nextHashedOwnerName));
    }

    /**
     * @dataProvider getDataProvider
     */
    public function testFactory(string $text, bool $unsignedDelegationsCovered, int $iterations, string $salt, string $nextOwnerName, array $types, string $nextHashedOwnerName): void
    {
        $nsec3 = Factory::NSEC3($unsignedDelegationsCovered, $iterations, $salt, $nextOwnerName, $types);
        $this->assertEquals($nextHashedOwnerName, NSEC3::base32encode($nsec3->getNextHashedOwnerName()));
        $this->assertEquals($text, $nsec3->toText());
    }
}
