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

namespace Badcow\DNS\Tests\Edns\Option;

use Badcow\DNS\Edns\Option\Codes;
use Badcow\DNS\Edns\Option\UnsupportedOptionException;
use PHPUnit\Framework\TestCase;

class CodesTest extends TestCase
{
    public function testIsValid(): void
    {
        $this->assertTrue(Codes::isValid(Codes::COOKIE));
        $this->assertTrue(Codes::isValid('COOKIE'));
    }

    public function testGetName(): void
    {
        $this->assertEquals('COOKIE', Codes::getName(Codes::COOKIE));
        $this->expectException(UnsupportedOptionException::class);
        $this->assertTrue(Codes::getName(1024));
    }
}
