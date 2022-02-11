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

    public const NAME = 'CLIENT_SUBNET';
    public const CODE = 8;

    public const FAMILY_IPV4 = 1;
    public const FAMILY_IPV6 = 2;

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

    public function setFamily(int $family): void
    {
        if ($family < 0 || $family > 0xFFFF) {
            throw new \DomainException(sprintf('Family must be an unsigned 16-bit integer. "%d" given.', $family));
        }
        $this->family = $family;
    }

    public function setSourceNetmask(int $sourceNetmask): void
    {
        if ($sourceNetmask < 0 || $sourceNetmask > 0xFF) {
            throw new \DomainException(sprintf('Source Netmask must be an unsigned 8-bit integer. "%d" given.', $sourceNetmask));
        }
        $this->sourceNetmask = $sourceNetmask;
    }

    public function getSourceNetmask(): ?int
    {
        return $this->sourceNetmask;
    }

    public function setScopeNetmask(int $scopeNetmask): void
    {
        if ($scopeNetmask < 0 || $scopeNetmask > 0xFF) {
            throw new \DomainException(sprintf('Scope Netmask must be an unsigned 8-bit integer. "%d" given.', $scopeNetmask));
        }
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
        if (!isset($this->family) || !isset($this->sourceNetmask) || !isset($this->address)) {
            throw new \InvalidArgumentException('Family, SourceNetmask, and Address must all be set.');
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

        if (!in_array($integers['family'], [self::FAMILY_IPV4, self::FAMILY_IPV6])) {
            throw new DecodeException(self::NAME, $optionValue);
        }

        if (self::FAMILY_IPV4 === $integers['family'] and ($integers['sourceNetmask'] > 32 or $integers['scopeNetmask'] > 32)) {
            throw new DecodeException(self::NAME, $optionValue);
        }
        $addressLength = self::FAMILY_IPV4 === $integers['family'] ? 4 : 16;
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
