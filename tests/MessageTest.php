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

use Badcow\DNS\Message;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    public function testWire1(): void
    {
        $data = file_get_contents(__DIR__.'/Resources/wire/wire_test.data1');
        $data = preg_replace(['/\#.*/', '/(?:\r|\n|\s)*/'], '', $data);
        $data = hex2bin($data);

        $msg = Message::fromWire($data);

        $this->assertInstanceOf(Message::class, $msg);
    }

    public function testWire2(): void
    {
        $data = file_get_contents(__DIR__.'/Resources/wire/wire_test.data2');
        $data = preg_replace(['/\#.*/', '/(?:\r|\n|\s)*/'], '', $data);
        $data = hex2bin($data);

        $msg = Message::fromWire($data);

        $this->assertInstanceOf(Message::class, $msg);
    }

    public function testWire3(): void
    {
        $data = file_get_contents(__DIR__.'/Resources/wire/wire_test.data3');
        $data = preg_replace(['/\#.*/', '/(?:\r|\n|\s)*/'], '', $data);
        $data = hex2bin($data);

        $msg = Message::fromWire($data);

        $this->assertInstanceOf(Message::class, $msg);
    }

    public function testWire4(): void
    {
        $data = file_get_contents(__DIR__.'/Resources/wire/wire_test.data4');
        $data = preg_replace(['/0x/', '/\#.*/', '/(?:\r|\n|\s)*/'], '', $data);
        $data = hex2bin($data);

        $msg = Message::fromWire($data);

        $this->assertInstanceOf(Message::class, $msg);
    }
}
