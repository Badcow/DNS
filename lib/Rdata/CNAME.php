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

class CNAME implements RdataInterface
{
    use RdataTrait;

    const TYPE = 'CNAME';

    /**
     * @var string
     */
    protected $target;

    /**
     * @param $target
     */
    public function setTarget(string $target)
    {
        $this->target = $target;
    }

    /**
     * @return string
     */
    public function getTarget(): string
    {
        return $this->target;
    }

    /**
     * {@inheritdoc}
     */
    public function output(): string
    {
        return $this->target;
    }
}
