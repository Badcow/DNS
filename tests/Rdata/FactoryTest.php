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
use Badcow\DNS\Rdata\UnsupportedTypeException;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    public function getTestData(): array
    {
        $namespace = '\\Badcow\\DNS\\Rdata\\';

        return [
            ['CNAME', 5, $namespace.'CNAME'],
            ['AAAA', 28, $namespace.'AAAA'],
            ['RRSIG', 46, $namespace.'RRSIG'],
        ];
    }

    /**
     * @dataProvider getTestData
     *
     * @throws UnsupportedTypeException
     */
    public function testNewRdataFromNameAndId(string $type, int $typeCode, string $classname): void
    {
        $this->assertInstanceOf($classname, Factory::newRdataFromName($type));
        $this->assertInstanceOf($classname, Factory::newRdataFromId($typeCode));
    }

    /**
     * @throws UnsupportedTypeException
     */
    public function testNewRdataFromNameThrowsExceptionForUnknownType(): void
    {
        $this->expectException(UnsupportedTypeException::class);
        Factory::newRdataFromName('rsig');
    }
}
