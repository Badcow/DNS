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

use Badcow\DNS\Algorithms;
use PHPUnit\Framework\TestCase;

class AlgorithmsTest extends TestCase
{
    public function testGetMnemonic(): void
    {
        $this->assertEquals('RSASHA1', Algorithms::getMnemonic(5));
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function testGetMnemonicThrowsExceptionOnInvalidAlgorithm(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('"1337" is not a valid algorithm.');
        Algorithms::getMnemonic(1337);
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function testGetAlgorithmValueThrowsExceptionOnInvalidMnemonic(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('"INVALID_MNEMONIC" is not a valid algorithm mnemonic.');
        Algorithms::getAlgorithmValue('INVALID_MNEMONIC');
    }
}
