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

namespace Badcow\DNS;

/**
 * Algorithms listed in {@link https://www.iana.org/assignments/dns-sec-alg-numbers/dns-sec-alg-numbers.xml}.
 */
class Algorithms
{
    /**
     * Delete DS.
     *
     * {@link https://tools.ietf.org/html/rfc8078#section-4}
     */
    public const DELETE = 0;

    /**
     * RSA/MD5.
     *
     * {@link https://tools.ietf.org/html/rfc4034#appendix-A.1}
     */
    public const RSAMD5 = 1;

    /**
     * Diffie-Hellman.
     *
     * {@link https://tools.ietf.org/html/rfc4034#appendix-A.1}
     */
    public const DH = 2;

    /**
     * DSA/SHA-1.
     *
     * {@link https://tools.ietf.org/html/rfc4034#appendix-A.1}
     */
    public const DSA = 3;

    /**
     * Elliptic Curve (Proposed).
     *
     * {@link https://tools.ietf.org/html/rfc4034#appendix-A.1}
     */
    public const ECC = 4;

    /**
     * RSA/SHA-1.
     *
     * {@link https://tools.ietf.org/html/rfc4034#appendix-A.1}
     */
    public const RSASHA1 = 5;

    /**
     * DSA-NSEC3-SHA1.
     *
     * {@link https://tools.ietf.org/html/RFC5155}
     */
    public const DSA_NSEC3_SHA1 = 6;

    /**
     * RSASHA1-NSEC3-SHA1.
     *
     * {@link https://tools.ietf.org/html/RFC5155}
     */
    public const RSASHA1_NSEC3_SHA1 = 7;

    /**
     * RSA/SHA-256.
     *
     * {@link https://tools.ietf.org/html/RFC5702}
     */
    public const RSASHA256 = 8;

    /**
     * RSA/SHA-512.
     *
     * {@link https://tools.ietf.org/html/RFC5702}.
     */
    public const RSASHA512 = 10;

    /**
     * GOST R 34.10-2001.
     *
     * {@link https://tools.ietf.org/html/RFC5933}.
     */
    public const ECC_GOST = 12;

    /**
     * ECDSA Curve P-256 with SHA-256.
     *
     * {@link https://tools.ietf.org/html/RFC6605}.
     */
    public const ECDSAP256SHA256 = 13;

    /**
     * ECDSA Curve P-384 with SHA-384.
     *
     * {@link https://tools.ietf.org/html/RFC6605}.
     */
    public const ECDSAP384SHA384 = 14;

    /**
     * Ed25519.
     *
     * {@link https://tools.ietf.org/html/RFC8080}.
     */
    public const ED25519 = 15;

    /**
     * Ed448.
     *
     * {@link https://tools.ietf.org/html/RFC8080}.
     */
    public const ED448 = 16;

    /**
     * Indirect.
     *
     * {@link https://tools.ietf.org/html/rfc4034#appendix-A.1}
     */
    public const INDIRECT = 252;

    /**
     * Private.
     *
     * {@link https://tools.ietf.org/html/rfc4034#appendix-A.1}
     */
    public const PRIVATEDNS = 253;

    /**
     * Private.
     *
     * {@link https://tools.ietf.org/html/rfc4034#appendix-A.1}
     */
    public const PRIVATEOID = 254;

    /**
     * @var array
     */
    public const MNEMONICS = [
        self::DELETE => 'DELETE',
        self::RSAMD5 => 'RSAMD5',
        self::DH => 'DH',
        self::DSA => 'DSA',
        self::ECC => 'ECC',
        self::RSASHA1 => 'RSASHA1',
        self::DSA_NSEC3_SHA1 => 'DSA-NSEC3-SHA1',
        self::RSASHA1_NSEC3_SHA1 => 'RSASHA1-NSEC3-SHA1',
        self::RSASHA256 => 'RSASHA256',
        self::RSASHA512 => 'RSASHA512',
        self::ECC_GOST => 'ECC-GOST',
        self::ECDSAP256SHA256 => 'ECDSAP256SHA256',
        self::ECDSAP384SHA384 => 'ECDSAP384SHA384',
        self::ED25519 => 'ED25519',
        self::ED448 => 'ED448',
        self::INDIRECT => 'INDIRECT',
        self::PRIVATEDNS => 'PRIVATEDNS',
        self::PRIVATEOID => 'PRIVATEOID',
    ];

    /**
     * Get the associated mnemonic of an algorithm.
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public static function getMnemonic(int $algorithmId)
    {
        if (!array_key_exists($algorithmId, self::MNEMONICS)) {
            throw new \InvalidArgumentException(sprintf('"%d" is not a valid algorithm.', $algorithmId));
        }

        return self::MNEMONICS[$algorithmId];
    }

    /**
     * @throws \InvalidArgumentException
     */
    public static function getAlgorithmValue(string $algorithmMnemonic): int
    {
        if (false === $keyTypeValue = array_search($algorithmMnemonic, self::MNEMONICS, true)) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid algorithm mnemonic.', $algorithmMnemonic));
        }

        return (int) $keyTypeValue;
    }
}
