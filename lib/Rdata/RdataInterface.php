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

interface RdataInterface
{
    /**
     * Get the string representation of the Rdata type.
     */
    public function getType(): string;

    /**
     * Get the integer index of the Rdata type.
     */
    public function getTypeCode(): int;

    /**
     * Return the string representation of the Rdata.
     */
    public function toText(): string;

    /**
     * Return a DNS Server response formatted representation of the Rdata.
     */
    public function toWire(): string;

    /**
     * Populate Rdata from its textual representation.
     */
    public function fromText(string $text): void;

    /**
     * Populate Rdata from its wire representation.
     *
     * @param int $rdLength
     */
    public function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): void;
}
