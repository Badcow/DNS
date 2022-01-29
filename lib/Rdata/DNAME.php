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
 * Class DnameRdata.
 *
 * The DNAME record provides redirection for a subtree of the domain
 * name tree in the DNS.  That is, all names that end with a particular
 * suffix are redirected to another part of the DNS.
 * Based on RFC6672
 *
 * @see http://tools.ietf.org/html/rfc6672
 */
class DNAME extends CNAME
{
    public const TYPE = 'DNAME';
    public const TYPE_CODE = 39;
}
