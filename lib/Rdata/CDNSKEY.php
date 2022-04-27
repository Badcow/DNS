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

/**
 * {@link https://tools.ietf.org/html/rfc7344#page-7}.
 */
class CDNSKEY extends DNSKEY
{
    public const TYPE = 'CDNSKEY';
    public const TYPE_CODE = 60;
}
