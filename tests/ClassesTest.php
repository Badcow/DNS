<?php

/*
 * This file is part of Badcow DNS Library.
 *
 * (c) Samuel Williams <sam@badcow.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Badcow\DNS\Tests;

use Badcow\DNS\Classes;

class ClassesTest extends \PHPUnit\Framework\TestCase
{
    public function testIsValidClass(): void
    {
        $this->assertTrue(Classes::isValid('IN'));
        $this->assertTrue(Classes::isValid('HS'));
        $this->assertTrue(Classes::isValid('CH'));

        $this->assertFalse(Classes::isValid('INTERNET'));
        $this->assertFalse(Classes::isValid('in'));
        $this->assertFalse(Classes::isValid('In'));
        $this->assertFalse(Classes::isValid('hS'));
    }
}
