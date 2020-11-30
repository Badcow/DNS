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
     *
     * @return string Rdata type, e.g. "A", "MX", "NS", etc.
     */
    public function getType(): string;

    /**
     * Get the integer type code of the Rdata type as defined by IANA.
     *
     * @return int IANA Rdata type code
     */
    public function getTypeCode(): int;

    /**
     * Return the string representation of the Rdata.
     *
     * @return string formatted Rdata output as would appear in BIND records
     */
    public function toText(): string;

    /**
     * Return a DNS Server response formatted representation of the Rdata.
     *
     * @return string packed binary form of Rdata
     */
    public function toWire(): string;

    /**
     * Populate Rdata object from its textual representation.
     *
     * @param string $text Rendered Rdata text to populate object
     */
    public function fromText(string $text): void;

    /**
     * Populate Rdata from its wire representation.
     *
     * @param string   $rdata    packed binary form of Rdata
     * @param int      $offset   the current offset or pointer, position of the start of Rdata relative to the whole $rdata string
     * @param int|null $rdLength the length of the Rdata string, if null it is taken to be the whole $rdata parameter string
     */
    public function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): void;
}
