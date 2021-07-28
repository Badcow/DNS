<?php

declare(strict_types=1);

/*
 * This file is part of Badcow DNS Library.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Badcow\DNS\Rdata;

/**
 * Class AliasRdata.
 *
 * The ALIAS record provides a non standard way to redirect a root domain,
 * as a CNAME is not allowed to.
 * This record is only usable by specific dns servers
 *
 * @see https://doc.powerdns.com/authoritative/guides/alias.html
 */
class ALIAS extends CNAME
{
    const TYPE = 'ALIAS';
}
