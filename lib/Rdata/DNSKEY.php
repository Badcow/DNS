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
 * Class DNSKEY.
 *
 * {@link https://tools.ietf.org/html/rfc4034#section-2.1}
 */
class DNSKEY extends KEY
{
    public const TYPE = 'DNSKEY';
    public const TYPE_CODE = 48;

    /**
     * The Protocol Field MUST have value 3, and the DNSKEY RR MUST be
     * treated as invalid during signature verification if it is found to be
     * some value other than 3.
     * {@link https://tools.ietf.org/html/rfc4034#section-2.1.2}.
     *
     * @var int
     */
    protected $protocol = 3;

    public function setProtocol(int $protocol): void
    {
        if (3 !== $protocol) {
            throw new \InvalidArgumentException('DNSKEY RData: parameter <protocol> can only be set to "3".');
        }

        parent::setProtocol($protocol);
    }
}
