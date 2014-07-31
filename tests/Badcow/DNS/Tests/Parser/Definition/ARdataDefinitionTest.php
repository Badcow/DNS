<?php
/*
 * This file is part of Badcow DNS Library.
 *
 * (c) Samuel Williams <sam@badcow.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Badcow\DNS\Tests\Parser\Definition;

use Badcow\DNS\Parser\Definition\ARdataDefinition;

class ARdataDefinitionTest extends \PHPUnit_Framework_TestCase
{
    private $valid_1 = 'A 3600 192.225.255.222';

    private $valid_data_1 = '192.225.255.222';

    private $valid_2 = 'A 192.15.255.222';

    private $valid_data_2 = '192.15.255.222';

    private $valid_3 = 'IN A 3600 138.77.255.222';

    private $valid_data_3 = '138.77.255.222';

    private $valid_4 = '@ IN   A     119.15.133.255';

    private $valid_data_4 = '119.15.133.255';

    private $valid_5 = 'sub.example.com HS A  119.15.133.255';

    private $valid_data_5 = '119.15.133.255';

    private $invalid_1 = '@ IN SOA  example.com. postmaster.example.com. 2014070201 3600 604800 14400 3600';

    private $invalid_2 = 'A 192.168.256.0';

    public function testIsValid()
    {
        $aRdataDefinition = new ARdataDefinition;

        $this->assertTrue($aRdataDefinition->isValid($this->valid_1));
        $this->assertTrue($aRdataDefinition->isValid($this->valid_2));
        $this->assertTrue($aRdataDefinition->isValid($this->valid_3));
        $this->assertTrue($aRdataDefinition->isValid($this->valid_4));
        $this->assertTrue($aRdataDefinition->isValid($this->valid_5));

        $this->assertFalse($aRdataDefinition->isValid($this->invalid_1));
        $this->assertFalse($aRdataDefinition->isValid($this->invalid_2));
    }

    public function testParse()
    {
        $aRdataDefinition = new ARdataDefinition;

        $this->assertInstanceOf('Badcow\DNS\Rdata\ARdata', $ar_1 = $aRdataDefinition->parse($this->valid_1));
        $this->assertEquals($this->valid_data_1, $ar_1->getAddress());

        $this->assertInstanceOf('Badcow\DNS\Rdata\ARdata', $ar_2 = $aRdataDefinition->parse($this->valid_2));
        $this->assertEquals($this->valid_data_2, $ar_2->getAddress());

        $this->assertInstanceOf('Badcow\DNS\Rdata\ARdata', $ar_3 = $aRdataDefinition->parse($this->valid_3));
        $this->assertEquals($this->valid_data_3, $ar_3->getAddress());

        $this->assertInstanceOf('Badcow\DNS\Rdata\ARdata', $ar_4 = $aRdataDefinition->parse($this->valid_4));
        $this->assertEquals($this->valid_data_4, $ar_4->getAddress());

        $this->assertInstanceOf('Badcow\DNS\Rdata\ARdata', $ar_5 = $aRdataDefinition->parse($this->valid_5));
        $this->assertEquals($this->valid_data_5, $ar_5->getAddress());
    }

    public function testParseException()
    {
        $aRdataDefinition = new ARdataDefinition;

        $this->setExpectedException('Badcow\DNS\Parser\ParseException');
        $aRdataDefinition->parse($this->invalid_1);

        $this->setExpectedException('Badcow\DNS\Parser\ParseException');
        $aRdataDefinition->parse($this->invalid_2);
    }
}