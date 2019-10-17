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

use Badcow\DNS\Rdata\A;
use PHPUnit\Framework\TestCase;

class ARdataTest extends TestCase
{
    /**
     * @var A
     */
    private $aRdata;

    public function setUp(): void
    {
        $this->aRdata = new A();
    }

    public function testGetType(): void
    {
        $this->assertEquals('A', $this->aRdata->getType());
    }

    public function testSetAddress(): void
    {
        $address = '192.168.1.1';
        $this->aRdata->setAddress($address);

        $this->assertEquals($address, $this->aRdata->getAddress());
    }

    public function testOutput(): void
    {
        $address = '192.168.1.1';
        $this->aRdata->setAddress($address);

        $this->assertEquals($address, $this->aRdata->output());
    }
}
