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

namespace Badcow\DNS\Rdata;

trait RdataToDigestableTrait
{
    /**
     * Return Rdata wire format which is also suitable for hashing.
     * @see RdataInterface::toWire()
     */
    public function toDigestable(string $origin): string
    {
        return $this->toWire($origin, true);
    }
}