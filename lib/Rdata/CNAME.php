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
 * @see https://tools.ietf.org/html/rfc1035#section-3.3.1
 */
class CNAME implements RdataInterface
{
    use RdataTrait;

    const TYPE = 'CNAME';

    /**
     * @var string|null
     */
    protected $target;

    /**
     * @param string $target
     */
    public function setTarget(string $target): void
    {
        $this->target = $target;
    }

    /**
     * @return string
     */
    public function getTarget(): ?string
    {
        return $this->target;
    }

    /**
     * {@inheritdoc}
     */
    public function output(): string
    {
        return $this->target ?? '';
    }
}
