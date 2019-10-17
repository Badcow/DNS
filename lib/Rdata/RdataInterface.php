<?php

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
     * @deprecated
     * @return string
     */
    public function output(): string;

    /**
     * Get the R-Data type.
     *
     * @return string
     */
    public function getType(): string;

    public function getTypeCode(): int;

    public function toText(): string;

    public function toWire(): string;

    public static function fromText(string $text): RdataInterface;

    public static function fromWire(string $rdata): RdataInterface;
}
