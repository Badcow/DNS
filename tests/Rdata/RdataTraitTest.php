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

use Badcow\DNS\Rdata\RdataTrait;
use PHPUnit\Framework\TestCase;

class RdataTraitTest extends TestCase
{
    use RdataTrait;

    public const TYPE = 'RDATA_TEST';

    public function testGetType(): void
    {
        $this->assertEquals(self::TYPE, $this->getType());
    }
}
