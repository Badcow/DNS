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
use Badcow\DNS\Validator;

/**
 * {@link https://tools.ietf.org/html/rfc4701}.
 */
class DHCID implements RdataInterface
{
    use RdataTrait;

    public const TYPE = 'DHCID';
    public const TYPE_CODE = 49;

    /**
     * 16-bit DHCID RR Identifier Type Code specifies what data from the DHCP
     * client's request was used as input into the hash function.
     *
     * @var int
     */
    private $identifierType;

    /**
     * The 1-octet 'htype' followed by 'hlen' octets of 'chaddr' from a DHCPv4
     * client's DHCPREQUEST.
     *
     * The data octets (i.e., the Type and Client-Identifier fields) from a
     * DHCPv4 client's Client Identifier option.
     *
     * The client's DUID (i.e., the data octets of a DHCPv6 client's Client
     * Identifier option or the DUID field from a DHCPv4 client's Client
     * Identifier option).
     *
     * @var string
     */
    private $identifier;

    /**
     * Hardware Type {@link https://tools.ietf.org/html/rfc2131}.
     *
     * @var int Hardware type used if identifier is DHCPv4 DHCPREQUEST carrying client hardware address (chaddr or MAC)
     */
    private $htype = 1;

    /**
     * The Fully Qualified Domain Name of the DHCP client.
     *
     * @var string
     */
    private $fqdn;

    /**
     * The digest type. Only one type is defined by IANA, SHA256 with value 1.
     *
     * @var int
     */
    private $digestType = 1;

    /**
     * The digest. This is calculated from the other parameters. Stored in raw binary.
     *
     * @var string
     */
    private $digest;

    public function setIdentifierType(int $identifierType): void
    {
        if (!Validator::isUnsignedInteger($identifierType, 16)) {
            throw new \InvalidArgumentException('Identifier type must be a 16-bit integer.');
        }
        $this->identifierType = $identifierType;
    }

    public function getIdentifierType(): int
    {
        return $this->identifierType;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getHtype(): int
    {
        return $this->htype;
    }

    public function setHtype(int $htype): void
    {
        if (!Validator::isUnsignedInteger($htype, 8)) {
            throw new \InvalidArgumentException('HType must be an 8-bit integer.');
        }
        $this->htype = $htype;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function setIdentifier(int $identifierType, string $identifier): void
    {
        $this->setIdentifierType($identifierType);
        $this->identifier = $identifier;
    }

    public function getFqdn(): string
    {
        return $this->fqdn;
    }

    public function setFqdn(string $fqdn): void
    {
        if (!Validator::fullyQualifiedDomainName($fqdn)) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a fully qualified domain name.', $fqdn));
        }
        $this->fqdn = $fqdn;
    }

    public function getDigestType(): int
    {
        return $this->digestType;
    }

    public function setDigestType(int $digestType): void
    {
        $this->digestType = $digestType;
    }

    /**
     * @return string Digest in raw binary
     */
    public function getDigest(): string
    {
        return $this->digest;
    }

    /**
     * @param string $digest Digest as raw binary
     */
    public function setDigest(string $digest): void
    {
        $this->digest = $digest;
    }

    /**
     * Calculate the digest from the identifier and fully qualified domain name already set on the object.
     *
     * @throws \BadMethodCallException
     */
    public function calculateDigest(): void
    {
        if (null === $this->identifier || null === $this->fqdn) {
            throw new \BadMethodCallException('Identifier and Fully Qualified Domain Name (FQDN) must both be set on DHCID object before calling calculateDigest().');
        }

        $fqdn = Message::encodeName($this->fqdn);
        $identifier = pack('H*', str_replace(':', '', strtolower($this->identifier)));
        if (0 === $this->identifierType) {
            $identifier = chr($this->htype).$identifier;
        }

        $this->digest = hash('sha256', $identifier.$fqdn, true);
    }

    /**
     * @throws \BadMethodCallException
     */
    public function toText(): string
    {
        return base64_encode($this->toWire());
    }

    public function toWire(): string
    {
        if (null === $this->digest) {
            $this->calculateDigest();
        }

        return pack('nC', $this->identifierType, $this->digestType).$this->digest;
    }

    /**
     * @throws \Exception
     */
    public function fromText(string $text): void
    {
        if (false === $decoded = base64_decode($text, true)) {
            throw new \Exception(sprintf('Unable to base64 decode text "%s".', $text));
        }

        $this->fromWire($decoded);
    }

    public function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): void
    {
        $rdLength = $rdLength ?? strlen($rdata);
        if (false === $integers = unpack('nIdentifierType/CDigestType', $rdata, $offset)) {
            throw new DecodeException(static::TYPE, $rdata);
        }

        $this->setIdentifierType((int) $integers['IdentifierType']);
        $this->setDigestType((int) $integers['DigestType']);
        $this->setDigest(substr($rdata, $offset + 3, $rdLength - 3));

        $offset += $rdLength;
    }
}
