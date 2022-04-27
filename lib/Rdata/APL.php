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

use PhpIP\IPBlock;
use PhpIP\IPv4Block;
use PhpIP\IPv6Block;

/**
 * @see https://tools.ietf.org/html/rfc3123
 */
class APL implements RdataInterface
{
    use RdataTrait;

    public const TYPE = 'APL';
    public const TYPE_CODE = 42;

    /**
     * @var IPBlock[]
     */
    private $includedAddressRanges = [];

    /**
     * @var IPBlock[]
     */
    private $excludedAddressRanges = [];

    /**
     * @param bool $included True if the resource exists within the range, False if the resource
     *                       is not within the range. I.E. the negation.
     */
    public function addAddressRange(IPBlock $ipBlock, $included = true): void
    {
        if ($included) {
            $this->includedAddressRanges[] = $ipBlock;
        } else {
            $this->excludedAddressRanges[] = $ipBlock;
        }
    }

    /**
     * @return IPBlock[]
     */
    public function getIncludedAddressRanges(): array
    {
        return $this->includedAddressRanges;
    }

    /**
     * @return IPBlock[]
     */
    public function getExcludedAddressRanges(): array
    {
        return $this->excludedAddressRanges;
    }

    public function toText(): string
    {
        $string = '';
        foreach ($this->includedAddressRanges as $ipBlock) {
            $string .= (4 === $ipBlock->getVersion()) ? '1:' : '2:';
            $string .= (string) $ipBlock.' ';
        }

        foreach ($this->excludedAddressRanges as $ipBlock) {
            $string .= (4 === $ipBlock->getVersion()) ? '!1:' : '!2:';
            $string .= (string) $ipBlock.' ';
        }

        return rtrim($string, ' ');
    }

    public function toWire(): string
    {
        $encoded = '';

        foreach ($this->includedAddressRanges as $ipBlock) {
            $encoded .= pack(
                'nCC',
                (4 === $ipBlock->getVersion()) ? 1 : 2,
                $ipBlock->getPrefixLength(),
                $ipBlock->getGivenIp()::NB_BYTES
            ).inet_pton((string) $ipBlock->getGivenIp());
        }

        foreach ($this->excludedAddressRanges as $ipBlock) {
            $encoded .= pack(
                'nCCC*',
                (4 === $ipBlock->getVersion()) ? 1 : 2,
                $ipBlock->getPrefixLength(),
                $ipBlock->getGivenIp()::NB_BYTES | 0b10000000
            ).inet_pton((string) $ipBlock->getGivenIp());
        }

        return $encoded;
    }

    /**
     * @throws \Exception
     */
    public function fromText(string $text): void
    {
        $iterator = new \ArrayIterator(explode(' ', $text));

        while ($iterator->valid()) {
            $matches = [];
            if (1 !== preg_match('/^(?<negate>!)?[1-2]:(?<block>.+)$/i', $iterator->current(), $matches)) {
                throw new \Exception(sprintf('"%s" is not a valid IP range.', $iterator->current()));
            }

            $ipBlock = IPBlock::create($matches['block']);
            $this->addAddressRange($ipBlock, '!' !== $matches['negate']);
            $iterator->next();
        }
    }

    public function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): void
    {
        $end = $offset + ($rdLength ?? strlen($rdata));

        while ($offset < $end) {
            if (false === $apItem = unpack('nfamily/Cprefix/Clength', $rdata, $offset)) {
                throw new DecodeException(static::TYPE, $rdata);
            }

            $isExcluded = (bool) ($apItem['length'] & 0b10000000);
            $len = $apItem['length'] & 0b01111111;
            $version = (1 === $apItem['family']) ? 4 : 6;
            $offset += 4;
            $address = substr($rdata, $offset, $len);
            $address = inet_ntop($address);
            $offset += $len;

            $ipBlock = (4 === $version) ? new IPv4Block($address, $apItem['prefix']) : new IPv6Block($address, $apItem['prefix']);
            $this->addAddressRange($ipBlock, !$isExcluded);
        }
    }
}
