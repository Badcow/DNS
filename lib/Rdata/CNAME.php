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
    const TYPE_CODE = 5;

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
    public function toText(): string
    {
        return $this->target ?? '';
    }

    /**
     * {@inheritdoc}
     */
    public function toWire(): string
    {
        if (null === $this->target) {
            throw new \InvalidArgumentException('Target must be set.');
        }

        return self::encodeName($this->target);
    }

    /**
     * {@inheritdoc}
     */
    public static function fromText(string $text): RdataInterface
    {
        $cname = new static();
        $cname->setTarget($text);

        return $cname;
    }

    /**
     * {@inheritdoc}
     */
    public static function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): RdataInterface
    {
        $cname = new static();
        $cname->setTarget(self::decodeName($rdata, $offset));

        return $cname;
    }
}
