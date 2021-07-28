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

trait RdataTrait
{
    /**
     * Get the string representation of the Rdata type.
     *
     * @return string Rdata type, e.g. "A", "MX", "NS", etc.
     */
    public function getType(): string
    {
        /* @const TYPE */
        return static::TYPE;
    }

    /**
     * Get the integer type code of the Rdata type as defined by IANA.
     *
     * @return int IANA Rdata type code
     */
    public function getTypeCode(): int
    {
        /* @const TYPE_CODE */
        return static::TYPE_CODE;
    }
}
