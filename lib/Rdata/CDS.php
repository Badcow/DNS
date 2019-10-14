<?php

/*
 * This file is part of Badcow DNS Library.
 *
 * (c) Samuel Williams <sam@badcow.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Badcow\DNS\Rdata;

// TODO: Implement CDS RData
class CDS implements RdataInterface
{
    use RdataTrait;

    const TYPE = 'CDS';
    const TYPE_CODE = 0;

    /**
     * {@inheritdoc}
     */
    public function output(): string
    {
        // TODO: Implement output() method.
    }
}
