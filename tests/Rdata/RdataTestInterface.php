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

namespace Badcow\DNS\Tests\Rdata;

interface RdataTestInterface
{
    public function testGetType();

    public function testGetTypeCode();

    public function testToText();

    public function testToWire();

    public function testFromText();

    public function testFromWire();

    public function testFactory();
}
