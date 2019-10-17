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

// TODO: Implement NSEC3PARAM RData
class NSEC3PARAM implements RdataInterface
{
    use RdataTrait;

    const TYPE = 'NSEC3PARAM';
    const TYPE_CODE = 0;

    /**
     * {@inheritdoc}
     */
    public function output(): string
    {
        // TODO: Implement output() method.
    }
}
