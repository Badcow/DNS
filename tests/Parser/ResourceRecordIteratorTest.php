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

namespace Badcow\DNS\Tests\Parser;

use Badcow\DNS\Parser\ResourceRecordIterator;
use PHPUnit\Framework\TestCase;

class ResourceRecordIteratorTest extends TestCase
{
    public function testNavigation(): void
    {
        $string = 'this is a string consisting of some words';
        $iterator = new ResourceRecordIterator($string);

        $this->assertEquals(0, $iterator->key());

        $iterator->end();
        $this->assertEquals('words', $iterator->current());

        $iterator->next();
        $this->assertFalse($iterator->valid());

        $iterator->prev();
        $this->assertEquals('words', $iterator->current());

        $iterator->seek(2);
        $this->assertEquals('a', $iterator->current());

        $iterator->prev();
        $this->assertEquals('is', $iterator->current());

        $iterator->seek(0);
        $this->assertEquals('this', $iterator->current());

        $this->expectException(\OutOfBoundsException::class);
        $iterator->prev();
    }

    public function testGetRemainingAsString(): void
    {
        $string = 'this is a string consisting of some words';
        $iterator = new ResourceRecordIterator($string);
        $iterator->seek(3);

        $this->assertEquals('string consisting of some words', $iterator->getRemainingAsString());
    }

    public function testToString(): void
    {
        $string = 'this is a string consisting of some words';
        $iterator = new ResourceRecordIterator($string);

        $this->assertEquals($string, (string) $iterator);
    }
}
