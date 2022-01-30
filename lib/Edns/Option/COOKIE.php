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

class COOKIE implements OptionInterface
{
    use OptionTrait;

    public const NAME = 'COOKIE';
    public const CODE = 10;

    /**
     * @var string|null
     */
    protected $clientCookie;

    /**
     * @var string|null
     */
    protected $serverCookie;

    public function getClientCookie(): ?string
    {
        return $this->clientCookie;
    }

    public function setClientCookie(?string $clientCookie): void
    {
        if (null !== $clientCookie and 8 != strlen($clientCookie)) {
            throw new \InvalidArgumentException('Length of client cookie must be 8 bytes');
        }
        $this->clientCookie = $clientCookie;
    }

    public function getServerCookie(): ?string
    {
        return $this->serverCookie;
    }

    public function setServerCookie(?string $serverCookie): void
    {
        if (null !== $serverCookie) {
            $length = strlen($serverCookie);
            if ($length < 8 or $length > 32) {
                throw new \InvalidArgumentException('Length of server cookie must be between 8 to 32 bytes');
            }
        }
        $this->serverCookie = $serverCookie;
    }

    public function toWire(): string
    {
        return $this->clientCookie.$this->serverCookie;
    }

    public function fromWire(string $optionValue, int &$offset = 0, ?int $optionLength = null): void
    {
        $optionLength = $optionLength ?? strlen($optionValue);
        if ($optionLength < 8 or (8 != $optionLength and ($optionLength < 16 or $optionLength > 40))) {
            throw new DecodeException(static::NAME, $optionValue);
        }
        $this->clientCookie = substr($optionValue, $offset, 8);
        $offset += 8;
        if ($optionLength > 8) {
            $this->serverCookie = substr($optionValue, $offset, $optionLength - 8);
        }
    }
}
