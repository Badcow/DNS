<?php

/*
 * This file is part of Badcow DNS Library.
 *
 * (c) Samuel Williams <sam@badcow.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Badcow\DNS\Reverse;

use Badcow\DNS\DNSException;
use Badcow\DNS\ResourceRecord;
use Badcow\DNS\Validator;

class ReverseRecord extends ResourceRecord
{
    public function setName($name)
    {
        if (!Validator::reverseIpv4($name) && Validator::reverseIpv6($name)) {
            throw new DNSException(sprintf('"%s" is not a valid reverse address.', $name));
        }

        $this->name = $name;
    }
}