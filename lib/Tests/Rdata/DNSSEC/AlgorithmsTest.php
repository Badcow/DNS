<?php

/*
 * This file is part of Badcow DNS Library.
 *
 * (c) Samuel Williams <sam@badcow.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Badcow\DNS\Tests\Rdata\DNSSEC;

use Badcow\DNS\Rdata\DNSSEC\Algorithms;
use PHPUnit\Framework\TestCase;

class AlgorithmsTest extends TestCase
{
    public function testGetMnemonic()
    {
        $this->assertEquals('RSASHA1', Algorithms::getMnemonic(5));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetMnemonicThrowsExceptionOnInvalidAlgorithm()
    {
        Algorithms::getMnemonic(1337);
    }
}