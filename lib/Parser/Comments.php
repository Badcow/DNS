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

namespace Badcow\DNS\Parser;

class Comments
{
    /**
     * No comments are parsed.
     */
    public const NONE = 0;

    /**
     * Inline comments that appear at the end of a resource record.
     */
    public const END_OF_ENTRY = 1;

    /**
     * Multi-line record comments: those comments that appear within multi-line brackets. E.g.
     * acme.org. IN MX (
     *      30       ;This comment will be parsed.
     *      mail-gw3 ;So will this comment.
     * ).
     */
    public const MULTILINE = 2;

    /**
     * Orphan comments appear without a resource record. Usually these are section headers.
     */
    public const ORPHAN = 4;

    /**
     * Parse all comments.
     */
    public const ALL = 7;
}
