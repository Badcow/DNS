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

// TODO: Implement CDNSKEY RData
class CDNSKEY implements RdataInterface
{
    use RdataTrait;

    const TYPE = 'CDNSKEY';
    const TYPE_CODE = 0;

    /**
     * {@inheritdoc}
     */
    public function toText(): string
    {
        // TODO: Implement output() method.
    }
}
