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
use Badcow\DNS\Parser\Tokens;
use Badcow\DNS\Validator;

/**
 * {@link https://tools.ietf.org/html/rfc4025}.
 */
class IPSECKEY implements RdataInterface
{
    use RdataTrait;

    public const TYPE = 'IPSECKEY';
    public const TYPE_CODE = 45;
    public const ALGORITHM_NONE = 0;
    public const ALGORITHM_DSA = 1;
    public const ALGORITHM_RSA = 2;
    public const ALGORITHM_ECDSA = 3;

    /**
     * This is an 8-bit precedence for this record.  It is interpreted in
     * the same way as the PREFERENCE field described in section 3.3.9 of
     * RFC 1035.
     *
     * Gateways listed in IPSECKEY records with lower precedence are to be
     * attempted first.  Where there is a tie in precedence, the order
     * should be non-deterministic.
     *
     * @var int
     */
    private $precedence;

    /**
     * The gateway type field indicates the format of the information that
     * is stored in the gateway field.
     *
     * The following values are defined:
     * - 0: No gateway is present.
     * - 1: A 4-byte IPv4 address is present.
     * - 2: A 16-byte IPv6 address is present.
     * - 3: A wire-encoded domain name is present.  The wire-encoded format is
     *      self-describing, so the length is implicit.  The domain name MUST
     *      NOT be compressed.  (See Section 3.3 of RFC 1035.)
     *
     * @var int
     */
    private $gatewayType;

    /**
     * 7-bit The algorithm type field identifies the public key's crypto-
     * graphic algorithm and determines the format of the public key field.
     * A value of 0 indicates that no key is present.
     *
     * The following values are defined:
     * - 1: A DSA key is present, in the format defined in RFC 2536.
     * - 2: A RSA key is present, in the format defined in RFC 3110.
     * - 3: An ECDSA key is present, in the format defined in RFC 6605.
     *
     * @var int
     */
    private $algorithm = 0;

    /**
     * The gateway field indicates a gateway to which an IPsec tunnel may be.
     * created in order to reach the entity named by this resource record.
     *
     * There are three formats:
     *
     * A 32-bit IPv4 address is present in the gateway field.  The data
     * portion is an IPv4 address as described in section 3.4.1 of RFC 1035.
     * This is a 32-bit number in network byte order.
     *
     * A 128-bit IPv6 address is present in the gateway field.  The data
     * portion is an IPv6 address as described in section 2.2 of RFC 3596
     * This is a 128-bit number in network byte order.
     *
     * The gateway field is a normal wire-encoded domain name, as described
     * in section 3.3 of RFC 1035. Compression MUST NOT be used.
     *
     * @var string|null
     */
    private $gateway;

    /**
     * Both the public key types defined in this document (RSA and DSA)
     * inherit their public key formats from the corresponding KEY RR
     * formats. Specifically, the public key field contains the
     * algorithm-specific portion of the KEY RR RDATA, which is all the KEY
     * RR DATA after the first four octets.  This is the same portion of the
     * KEY RR that must be specified by documents that define a DNSSEC
     * algorithm. Those documents also specify a message digest to be used
     * for generation of SIG RRs; that specification is not relevant for
     * IPSECKEY RRs.
     *
     * @var string|null
     */
    private $publicKey = null;

    public function getPrecedence(): int
    {
        return $this->precedence;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function setPrecedence(int $precedence): void
    {
        if (!Validator::isUnsignedInteger($precedence, 8)) {
            throw new \InvalidArgumentException('IPSECKEY precedence must be an 8-bit integer.');
        }
        $this->precedence = $precedence;
    }

    public function getGatewayType(): int
    {
        return $this->gatewayType;
    }

    public function getAlgorithm(): int
    {
        return $this->algorithm;
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function setAlgorithm(int $algorithm): void
    {
        if (!Validator::isUnsignedInteger($algorithm, 8)) {
            throw new \InvalidArgumentException('IPSECKEY algorithm type must be an 8-bit integer.');
        }
        $this->algorithm = $algorithm;
    }

    public function getGateway(): ?string
    {
        return $this->gateway;
    }

    /**
     * @param string|null $gateway either &null for no gateway, a fully qualified domain name, or an IPv4 or IPv6 address
     *
     * @throws \InvalidArgumentException
     */
    public function setGateway(?string $gateway): void
    {
        if (null === $gateway || '.' === $gateway) {
            $gateway = null;
            $this->gatewayType = 0;
        } elseif (Validator::ipv4($gateway)) {
            $this->gatewayType = 1;
        } elseif (Validator::ipv6($gateway)) {
            $this->gatewayType = 2;
        } elseif (Validator::fullyQualifiedDomainName($gateway)) {
            $this->gatewayType = 3;
        } else {
            throw new \InvalidArgumentException('The gateway must be a fully qualified domain name, null, or a valid IPv4 or IPv6 address.');
        }

        $this->gateway = $gateway;
    }

    /**
     * @return string|null base64 encoded public key
     */
    public function getPublicKey(): ?string
    {
        return $this->publicKey;
    }

    /**
     * @param int         $algorithm either IPSECKEY::ALGORITHM_NONE, IPSECKEY::ALGORITHM_DSA, IPSECKEY::ALGORITHM_RSA, or IPSECKEY::ALGORITHM_ECDSA
     * @param string|null $publicKey base64 encoded public key
     *
     * @throws \InvalidArgumentException
     */
    public function setPublicKey(int $algorithm, ?string $publicKey): void
    {
        $this->publicKey = $publicKey;
        $this->setAlgorithm((null === $publicKey) ? 0 : $algorithm);
    }

    public function toText(): string
    {
        return rtrim(sprintf(
            '%d %d %d %s %s',
            $this->precedence,
            $this->gatewayType,
            $this->algorithm,
            (0 === $this->gatewayType) ? '.' : $this->gateway,
            (0 === $this->algorithm) ? '' : base64_encode($this->publicKey ?? '')
        ));
    }

    public function toWire(): string
    {
        $wire = pack('CCC', $this->precedence, $this->gatewayType, $this->algorithm);
        if (1 === $this->gatewayType || 2 === $this->gatewayType) {
            if (null === $this->gateway) {
                throw new \RuntimeException('Gateway cannot be null if IPSECKEY::gatewayType > 0.');
            }
            $wire .= inet_pton($this->gateway);
        } else {
            $wire .= Message::encodeName($this->gateway ?? '.');
        }

        if (self::ALGORITHM_NONE !== $this->algorithm && null !== $this->publicKey) {
            $wire .= $this->publicKey;
        }

        return $wire;
    }

    public function fromText(string $text): void
    {
        $rdata = explode(Tokens::SPACE, $text);
        $this->setPrecedence((int) array_shift($rdata));
        array_shift($rdata); //Gateway type is inferred from setGateway.
        $algorithm = (int) array_shift($rdata);
        $this->setGateway((string) array_shift($rdata));
        $publicKey = (0 === $algorithm) ? null : base64_decode(implode('', $rdata));
        $this->setPublicKey($algorithm, $publicKey);
    }

    /**
     * @throws DecodeException
     */
    public function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): void
    {
        $end = $offset + ($rdLength ?? strlen($rdata));

        if (false === $integers = unpack('CPrecedence/CGatewayType/CAlgorithm', $rdata, $offset)) {
            throw new DecodeException(static::TYPE, $rdata);
        }
        $offset += 3;
        $this->setPrecedence((int) $integers['Precedence']);
        $gatewayType = $integers['GatewayType'];
        $algorithm = $integers['Algorithm'];

        $this->setGateway(self::extractGateway($gatewayType, $rdata, $offset));

        if (self::ALGORITHM_NONE !== $algorithm) {
            $this->setPublicKey($algorithm, substr($rdata, $offset, $end - $offset));
        }

        $offset = $end;
    }

    /**
     * @throws DecodeException
     */
    private static function extractGateway(int $gatewayType, string $rdata, int &$offset): string
    {
        switch ($gatewayType) {
            case 0:
            case 3:
                $gateway = Message::decodeName($rdata, $offset);
                break;
            case 1:
                $gateway = @inet_ntop(substr($rdata, $offset, 4));
                $offset += 4;
                break;
            case 2:
                $gateway = @inet_ntop(substr($rdata, $offset, 16));
                $offset += 16;
                break;
            default:
                $invalidArgumentException = new \InvalidArgumentException(sprintf('Expected gateway type to be integer on [0,3], got "%d".', $gatewayType));
                throw new DecodeException(static::TYPE, $rdata, 0, $invalidArgumentException);
        }

        if (false === $gateway) {
            throw new DecodeException(static::TYPE, $rdata);
        }

        return $gateway;
    }
}
