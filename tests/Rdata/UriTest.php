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

use Badcow\DNS\Rdata\Factory;
use Badcow\DNS\Rdata\URI;
use PHPUnit\Framework\TestCase;

class UriTest extends TestCase
{
    public function dataProvider_testExceptions(): array
    {
        return [
            //[Priority, Weight, Target, ExpectedException, ExpectedExceptionMessage]
            [-1, 1, 'http://www.example.com/path', \InvalidArgumentException::class, 'Priority must be an unsigned integer on the range [0-65535]'],
            [0x10000, 10, 'https://tools.ietf.org/html/rfc7553', \InvalidArgumentException::class, 'Priority must be an unsigned integer on the range [0-65535]'],
            [10, -1, 'http://www.example.com/path', \InvalidArgumentException::class, 'Weight must be an unsigned integer on the range [0-65535]'],
            [256, 0x10000, 'https://tools.ietf.org/html/rfc7553', \InvalidArgumentException::class, 'Weight must be an unsigned integer on the range [0-65535]'],
            [10, 0xff, '"https://tools.ietf.org/html/rfc7553"', \InvalidArgumentException::class, 'The target ""https://tools.ietf.org/html/rfc7553"" is not a valid URI.'],
        ];
    }

    public function testOutput(): void
    {
        $srv = Factory::URI(10, 1, 'http://www.example.com/path');
        $expectation = '10 1 "http://www.example.com/path"';

        $this->assertEquals($expectation, $srv->output());
        $this->assertEquals(10, $srv->getPriority());
        $this->assertEquals(1, $srv->getWeight());
        $this->assertEquals('http://www.example.com/path', $srv->getTarget());
    }

    /**
     * @dataProvider dataProvider_testExceptions
     *
     * @param int    $priority
     * @param int    $weight
     * @param string $target
     * @param string $expectedException
     * @param string $expectedExceptionMessage
     */
    public function testExceptions(int $priority, int $weight, string $target, string $expectedException, string $expectedExceptionMessage): void
    {
        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedExceptionMessage);

        Factory::URI($priority, $weight, $target);
    }
}