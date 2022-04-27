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

use Badcow\DNS\Edns\Option\UnknownOption;
use PHPUnit\Framework\TestCase;

class UnknownOptionTest extends TestCase
{
    /**
     * @var UnknownOption
     */
    private $option;

    public function setUp(): void
    {
        $this->option = new UnknownOption();
    }

    public function testGetterSetters(): void
    {
        $this->option->setOptionCode(123);
        $this->assertEquals(123, $this->option->getNameCode());
        $this->assertEquals('OPTION123', $this->option->getName());
    }

    public function testToWire(): void
    {
        $noData = new UnknownOption();
        $this->assertEquals('', $noData->toWire());

        $withData = new UnknownOption();
        $withData->setData('HelloWorld');
        $this->assertEquals('HelloWorld', $withData->toWire());
    }

    public function testFromWire1(): void
    {
        $wire = '';
        $noData = new UnknownOption();
        $noData->fromWire($wire);
        $this->assertEmpty($noData->getData());
    }

    public function testFromWire2(): void
    {
        $wire = 'HelloWorld';
        $withData = new UnknownOption();
        $withData->fromWire($wire);
        $this->assertEquals('HelloWorld', $withData->getData());
    }
}
