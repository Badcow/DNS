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

use Badcow\DNS\Rdata\CAA;
use Badcow\DNS\Rdata\Factory;

class CaaRdataTest extends \PHPUnit\Framework\TestCase
{
    public function testOutput(): void
    {
        $caa = Factory::Caa(0, 'issue', 'letsencrypt.org');

        $expectation = '0 issue "letsencrypt.org"';

        $this->assertEquals($expectation, $caa->output());
        $this->assertEquals(0, $caa->getFlag());
        $this->assertEquals('issue', $caa->getTag());
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function testFlagException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Flag must be an unsigned integer on the range [0-255]');

        $srv = new CAA();
        $srv->setFlag(CAA::MAX_FLAG + 1);
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function testTagException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Tag can be one of this type "issue", "issuewild", or "iodef".');

        $srv = new CAA();
        $srv->setTag('not_exist');
    }

    public function testGetType(): void
    {
        $this->assertEquals('CAA', (new CAA())->getType());
    }
}
