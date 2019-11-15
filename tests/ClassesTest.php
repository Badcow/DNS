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

    public function testGetClassId(): void
    {
        $this->assertEquals(1, Classes::getClassId('IN'));
        $this->assertEquals(4, Classes::getClassId('HS'));
        $this->assertEquals(3, Classes::getClassId('CH'));
    }

    public function testGetClassIdThrowsExceptionForUndefinedClass(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Class "XX" is not a valid DNS class.');
        Classes::getClassId('XX');
    }
}
