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

use Badcow\DNS\Rdata\Types;
use Badcow\DNS\Rdata\UnsupportedTypeException;
use PHPUnit\Framework\TestCase;

class TypesTest extends TestCase
{
    /**
     * @throws UnsupportedTypeException
     */
    public function testGetTypeCode(): void
    {
        $this->assertEquals(1, Types::getTypeCode('A'));
        $this->assertEquals(1234, Types::getTypeCode('TYPE1234'));

        $this->expectException(UnsupportedTypeException::class);
        $this->expectExceptionMessage('RData type "XX" is not supported.');
        Types::getTypeCode('XX');
    }
}
