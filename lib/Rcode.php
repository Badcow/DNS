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

class Rcode
{
    /**
     * No Error [RFC1035].
     */
    const NOERROR = 0;

    /**
     * Format Error [RFC1035].
     */
    const FORMERR = 1;

    /**
     * Server Failure [RFC1035].
     */
    const SERVFAIL = 2;

    /**
     * Non-Existent Domain [RFC1035].
     */
    const NXDOMAIN = 3;

    /**
     * Not Implemented [RFC1035].
     */
    const NOTIMP = 4;

    /**
     * Query Refused [RFC1035].
     */
    const REFUSED = 5;

    /**
     * Name Exists when it should not [RFC2136][RFC6672].
     */
    const YXDOMAIN = 6;

    /**
     * RR Set Exists when it should not [RFC2136].
     */
    const YXRRSET = 7;

    /**
     * RR Set that should exist does not [RFC2136].
     */
    const NXRRSET = 8;

    /**
     * Server Not Authoritative for zone [RFC2136].
     * Not Authorized [RFC2845].
     */
    const NOTAUTH = 9;

    /**
     * Name not contained in zone [RFC2136].
     */
    const NOTZONE = 10;

    /**
     * DSO-TYPE Not Implemented [RFC8490].
     */
    const DSOTYPENI = 11;

    /**
     * Bad OPT Version [RFC6891].
     */
    const BADVERS = 16;

    /**
     * TSIG Signature Failure [RFC2845].
     */
    const BADSIG = 16;

    /**
     * Key not recognized [RFC2845].
     */
    const BADKEY = 17;

    /**
     * Signature out of time window [RFC2845].
     */
    const BADTIME = 18;

    /**
     * Bad TKEY Mode [RFC2930].
     */
    const BADMODE = 19;

    /**
     * Duplicate key name [RFC2930].
     */
    const BADNAME = 20;

    /**
     * Algorithm not supported [RFC2930].
     */
    const BADALG = 21;

    /**
     * Bad Truncation [RFC4635].
     */
    const BADTRUNC = 22;

    /**
     * Bad/missing Server Cookie [RFC7873].
     */
    const BADCOOKIE = 23;
}
