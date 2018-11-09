<?php
/**
 * Created by PhpStorm.
 * User: Samuel Williams
 * Date: 10/11/2018
 * Time: 7:24 AM
 */

namespace Badcow\DNS\Tests\Rdata;

use PHPUnit\Framework\TestCase;
use Badcow\DNS\Rdata\Factory;

class FactoryTest extends TestCase
{
    public function testNewRdataFromName()
    {
        $namespace = '\\Badcow\\DNS\\Rdata\\';
        $this->assertInstanceOf($namespace.'CNAME', Factory::newRdataFromName('cname'));
        $this->assertInstanceOf($namespace.'AAAA', Factory::newRdataFromName('Aaaa'));
        $this->assertInstanceOf($namespace.'DNSSEC\\RRSIG', Factory::newRdataFromName('rrsig'));

        $this->expectException(\InvalidArgumentException::class);
        Factory::newRdataFromName('rsig');
    }
}