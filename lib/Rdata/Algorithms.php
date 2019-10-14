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
 * Algorithms listed in {@link https://tools.ietf.org/html/rfc4034#appendix-A.1}.
 */
class Algorithms
{
    /**
     * RSA/MD5.
     */
    const RSAMD5 = 1;

    /**
     * Diffie-Hellman.
     */
    const DH = 2;

    /**
     * DSA/SHA-1.
     */
    const DSA = 3;

    /**
     * Elliptic Curve.
     */
    const ECC = 4;

    /**
     * RSA/SHA-1.
     */
    const RSASHA1 = 5;

    /**
     * Indirect.
     */
    const INDIRECT = 252;

    /**
     * Private.
     */
    const PRIVATEDNS = 253;

    /**
     * Private.
     */
    const PRIVATEOID = 254;

    /**
     * @var array
     */
    private static $mnemonic = [
        self::RSAMD5 => 'RSAMD5',
        self::DH => 'DH',
        self::DSA => 'DSA',
        self::ECC => 'ECC',
        self::RSASHA1 => 'RSASHA1',
        self::INDIRECT => 'INDIRECT',
        self::PRIVATEDNS => 'PRIVATEDNS',
        self::PRIVATEOID => 'PRIVATEOID',
    ];

    /**
     * Get the associated mnemonic of an algorithm.
     *
     * @param int $algorithmId
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public static function getMnemonic(int $algorithmId)
    {
        if (!array_key_exists($algorithmId, self::$mnemonic)) {
            throw new \InvalidArgumentException(sprintf('"%d" is not a valid algorithm.', $algorithmId));
        }

        return self::$mnemonic[$algorithmId];
    }
}
