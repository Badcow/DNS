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
 * @see https://www.rfc-editor.org/rfc/rfc7871.html#section-6
 */
class CLIENT_SUBNET implements OptionInterface
{
    use OptionTrait;

    const NAME = 'CLIENT_SUBNET';
    const NAME_CODE = 8;

    const FAMILIY_IPV4 = 1;
    const FAMILIY_IPV6 = 2;

    /**
     * @var int|null
     */
    protected $family;

    /**
     * @var int|null
     */
    protected $sourceNetmask;

    /**
     * @var int|null
     */
    protected $scopeNetmask;

    /**
     * @var string|null
     */
    protected $address;

    public function getFamily(): ?int
    {
        return $this->family;
    }

    public function setFamily(?int $family): void
    {
        $this->family = $family;
    }

    public function setSourceNetmask(?int $sourceNetmask): void
    {
        $this->sourceNetmask = $sourceNetmask;
    }

    public function getSourceNetmask(): ?int
    {
        return $this->sourceNetmask;
    }

    public function setScopeNetmask(?int $scopeNetmask): void
    {
        $this->scopeNetmask = $scopeNetmask;
    }

    public function getScopeNetmask(): ?int
    {
        return $this->scopeNetmask;
    }

    public function setAddress(?string $address): void
    {
        $this->address = $address;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function toWire(): string
    {
        if (is_null($this->family) or is_null($this->sourceNetmask) or is_null($this->address)) {
            throw new \Exception();
        }

        return pack('ncc', $this->family, $this->sourceNetmask, $this->scopeNetmask ?? 0).inet_pton($this->address);
    }

    public function fromWire(string $optionValue, int &$offset = 0, ?int $optionLength = null): void
    {
        $integers = unpack('nfamily/csourceNetmask/cscopeNetmask', $optionValue, $offset);
        if (false === $integers) {
            throw new DecodeException(self::NAME, $optionValue);
        }
        $offset += 4;
        if (!in_array($integers['family'], [self::FAMILIY_IPV4, self::FAMILIY_IPV6])) {
            throw new DecodeException(self::NAME, $optionValue);
        }
        if (self::FAMILIY_IPV4 === $integers['family'] and ($integers['sourceNetmask'] > 32 or $integers['scopeNetmask'] > 32)) {
            throw new DecodeException(self::NAME, $optionValue);
        }
        $addressLength = self::FAMILIY_IPV4 === $integers['family'] ? 4 : 16;
        $address = @inet_ntop(substr($optionValue, $offset, $addressLength));
        if (false === $address) {
            throw new DecodeException(self::NAME, $optionValue);
        }
        $offset += $addressLength;
        $this->family = $integers['family'];
        $this->sourceNetmask = $integers['sourceNetmask'];
        $this->scopeNetmask = $integers['scopeNetmask'];
        $this->address = $address;
    }
}
