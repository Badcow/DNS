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

use Badcow\DNS\Message;

/**
 * @see https://tools.ietf.org/html/rfc1035#section-3.3.1
 */
class CNAME implements RdataInterface
{
    use RdataTrait;

    public const TYPE = 'CNAME';
    public const TYPE_CODE = 5;

    /**
     * @var string|null
     */
    protected $target;

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

    public function toText(): string
    {
        return $this->target ?? '';
    }

    public function toWire(): string
    {
        if (null === $this->target) {
            throw new \InvalidArgumentException('Target must be set.');
        }

        return Message::encodeName($this->target);
    }

    public function fromText(string $text): void
    {
        $this->setTarget($text);
    }

    public function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): void
    {
        $this->setTarget(Message::decodeName($rdata, $offset));
    }
}
