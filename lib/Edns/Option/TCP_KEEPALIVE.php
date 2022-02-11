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

namespace Badcow\DNS\Edns\Option;

/**
 * @see https://www.rfc-editor.org/rfc/rfc7828.html#section-3.1
 */
class TCP_KEEPALIVE implements OptionInterface
{
    use OptionTrait;

    public const NAME = 'TCP_KEEPALIVE';
    public const CODE = 11;

    /**
     * @var int|null
     */
    protected $timeout;

    public function getTimeout(): ?int
    {
        return $this->timeout;
    }

    public function setTimeout(?int $timeout): void
    {
        $this->timeout = $timeout;
    }

    public function toWire(): string
    {
        if (null === $this->timeout) {
            return '';
        }

        return pack('n', $this->timeout);
    }

    public function fromWire(string $optionValue, int &$offset = 0, ?int $optionLength = null): void
    {
        $optionLength = $optionLength ?? strlen($optionValue);
        if (0 !== $optionLength and 2 !== $optionLength) {
            throw new DecodeException(self::NAME, $optionValue);
        }
        if (2 === $optionLength) {
            $integers = unpack('ntimeout', $optionValue, $offset);
            if (false === $integers) {
                throw new DecodeException(self::NAME, $optionValue);
            }
            $offset += 2;
            $this->timeout = $integers['timeout'];
        }
    }
}
