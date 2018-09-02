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

use Badcow\DNS\Rdata\RdataTrait;


class RdataTraitTest extends \PHPUnit\Framework\TestCase
{
    use RdataTrait;

    const TYPE = 'RDATA_TEST';

    public function testGetType()
    {
        $this->assertEquals(self::TYPE, $this->getType());
    }
}
