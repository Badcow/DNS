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

use Badcow\DNS\Rdata\MX;


class MxRdataTest extends \PHPUnit\Framework\TestCase
{
    public function testSetters()
    {
        $target = 'foo.example.com.';
        $preference = 10;
        $mx = new MX();
        $mx->setExchange($target);
        $mx->setPreference($preference);

        $this->assertEquals($target, $mx->getExchange());
        $this->assertEquals($preference, $mx->getPreference());
    }

    public function testOutput()
    {
        $target = 'foo.example.com.';
        $mx = new MX();
        $mx->SetExchange($target);
        $mx->setPreference(42);

        $this->assertEquals('42 foo.example.com.', $mx->output());
    }
}
