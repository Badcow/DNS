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

use Badcow\DNS\Rdata\RdataInterface;
use Badcow\DNS\Rdata\RdataTrait;

class DummyRdata implements RdataInterface
{
    use RdataTrait;

    const TYPE = 'DUMMY';

    /**
     * {@inheritdoc}
     */
    public function toText(): string
    {
        return 'co.badcow.dns.test.dummy';
    }

    public function toWire(): string
    {
        // TODO: Implement toWire() method.
    }

    public static function fromText(string $text): RdataInterface
    {
        // TODO: Implement fromText() method.
    }

    public static function fromWire(string $rdata): RdataInterface
    {
        // TODO: Implement fromWire() method.
    }
}
