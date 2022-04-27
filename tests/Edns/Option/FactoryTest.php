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
use Badcow\DNS\Edns\Option\COOKIE;
use Badcow\DNS\Edns\Option\Factory;
use Badcow\DNS\Edns\Option\UnsupportedOptionException;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    public function testNewOptionFromName(): void
    {
        $this->assertInstanceOf(COOKIE::class, Factory::newOptionFromName('COOKIE'));

        $this->expectException(UnsupportedOptionException::class);
        Factory::newOptionFromName('INVALID');
    }

    public function testIsOptionCodeImplemented(): void
    {
        $this->assertTrue(Factory::isOptionCodeImplemented(Codes::COOKIE));
        $this->assertFalse(Factory::isOptionCodeImplemented(1024));
    }
}
