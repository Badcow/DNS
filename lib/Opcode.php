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

namespace Badcow\DNS;

/**
 * {@link https://www.iana.org/assignments/dns-parameters/dns-parameters.xhtml#dns-parameters-5}.
 */
class Opcode
{
    /**
     * [RFC1035].
     */
    public const QUERY = 0;

    /**
     * Inverse Query [RFC3425] (Obsolete).
     */
    public const IQUERY = 1;

    /**
     * [RFC1035].
     */
    public const STATUS = 2;

    /**
     * [RFC1996].
     */
    public const NOTIFY = 4;

    /**
     * [RFC2136].
     */
    public const UPDATE = 5;

    /**
     * DNS Stateful Operations [RFC8490].
     */
    public const DSO = 6;
}
