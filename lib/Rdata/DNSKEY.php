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
 * Class DNSKEY.
 *
 * {@link https://tools.ietf.org/html/rfc4034#section-2.1}
 */
class DNSKEY extends KEY
{
    const TYPE = 'DNSKEY';
    const TYPE_CODE = 48;

    /**
     * {@inheritdoc}
     */
    protected $protocol = 3;
}
