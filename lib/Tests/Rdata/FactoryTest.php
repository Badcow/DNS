<?php

/*
 * This file is part of Badcow DNS Library.
 *
 * (c) Samuel Williams <sam@badcow.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Badcow\DNS\Tests\Rdata;

use Badcow\DNS\Rdata\UnsupportedTypeException;
use PHPUnit\Framework\TestCase;
use Badcow\DNS\Rdata\Factory;

class FactoryTest extends TestCase
{
    /**
     * @throws UnsupportedTypeException
     */
    public function testNewRdataFromName()
    {
        $namespace = '\\Badcow\\DNS\\Rdata\\';
        $this->assertInstanceOf($namespace.'CNAME', Factory::newRdataFromName('cname'));
        $this->assertInstanceOf($namespace.'AAAA', Factory::newRdataFromName('Aaaa'));
        $this->assertInstanceOf($namespace.'DNSSEC\\RRSIG', Factory::newRdataFromName('rrsig'));

        $this->expectException(UnsupportedTypeException::class);
        Factory::newRdataFromName('rsig');
    }
}
