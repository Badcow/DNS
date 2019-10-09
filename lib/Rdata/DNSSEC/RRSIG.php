<?php

/*
 * This file is part of Badcow DNS Library.
 *
 * (c) Samuel Williams <sam@badcow.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Badcow\DNS\Rdata\DNSSEC;

class RRSIG extends \Badcow\DNS\Rdata\RRSIG
{
    public function __construct()
    {
        @trigger_error('Classes in Badcow\\DNS\\Rdata\\DNSSEC namespace are deprecated. Use Classes in Badcow\\DNS\\Rdata namespace instead.', E_USER_DEPRECATED);
    }
}
