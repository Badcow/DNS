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


use Badcow\DNS\Rdata\RdataInterface;
use Badcow\DNS\Rdata\RdataTrait;

class NsecRdata implements RdataInterface
{
    use RdataTrait;

    const TYPE = 'NSEC';

    public function output()
    {
        // TODO: Implement output() method.
    }
}