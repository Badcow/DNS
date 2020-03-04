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
     * @return string
     */
    public function getType(): string;

    /**
     * Get the integer index of the Rdata type.
     *
     * @return int
     */
    public function getTypeCode(): int;

    /**
     * Return the string representation of the Rdata.
     *
     * @return string
     */
    public function toText(): string;

    /**
     * Return a DNS Server response formatted representation of the Rdata.
     *
     * @return string
     */
    public function toWire(): string;

    /**
     * Return an instance of Rdata from its textual representation.
     *
     * @param string $text
     *
     * @return RdataInterface
     */
    public static function fromText(string $text): RdataInterface;

    /**
     * Return an instance of Rdata from its wire representation.
     *
     * @param string $rdata
     * @param int    $offset
     * @param int    $rdLength
     *
     * @return RdataInterface
     */
    public static function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): RdataInterface;
}
