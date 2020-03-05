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

    const TYPE = 'APL';
    const TYPE_CODE = 42;

    /**
     * @var IPBlock[]
     */
    private $includedAddressRanges = [];

    /**
     * @var IPBlock[]
     */
    private $excludedAddressRanges = [];

    /**
     * @param IPBlock $ipBlock
     * @param bool    $included True if the resource exists within the range, False if the resource
     *                          is not within the range. I.E. the negation.
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

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    public function toWire(): string
    {
        $encoded = '';

        foreach ($this->includedAddressRanges as $ipBlock) {
            $encoded .= pack('nCC',
                (4 === $ipBlock->getVersion()) ? 1 : 2,
                $ipBlock->getPrefix(),
                $ipBlock->getGivenIp()::NB_BYTES
            ).inet_pton((string) $ipBlock->getGivenIp());
        }

        foreach ($this->excludedAddressRanges as $ipBlock) {
            $encoded .= pack('nCCC*',
                (4 === $ipBlock->getVersion()) ? 1 : 2,
                $ipBlock->getPrefix(),
                $ipBlock->getGivenIp()::NB_BYTES | 0b10000000
            ).inet_pton((string) $ipBlock->getGivenIp());
        }

        return $encoded;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public static function fromText(string $text): RdataInterface
    {
        $iterator = new \ArrayIterator(explode(' ', $text));
        $apl = new self();

        while ($iterator->valid()) {
            $matches = [];
            if (1 !== preg_match('/^(?<negate>!)?[1-2]:(?<block>.+)$/i', $iterator->current(), $matches)) {
                throw new \Exception(sprintf('"%s" is not a valid IP range.', $iterator->current()));
            }

            $ipBlock = IPBlock::create($matches['block']);
            $apl->addAddressRange($ipBlock, '!' !== $matches['negate']);
            $iterator->next();
        }

        return $apl;
    }

    /**
     * {@inheritdoc}
     */
    public static function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): RdataInterface
    {
        $apl = new self();

        $end = $offset + ($rdLength ?? strlen($rdata));

        while ($offset < $end) {
            $apItem = unpack('nfamily/Cprefix/Clength', $rdata, $offset);
            $isExcluded = (bool) ($apItem['length'] & 0b10000000);
            $len = $apItem['length'] & 0b01111111;
            $version = (1 === $apItem['family']) ? 4 : 6;
            $offset += 4;
            $address = substr($rdata, $offset, $len);
            $address = inet_ntop($address);
            $offset += $len;

            $ipBlock = (4 === $version) ? new IPv4Block($address, $apItem['prefix']) : new IPv6Block($address, $apItem['prefix']);
            $apl->addAddressRange($ipBlock, !$isExcluded);
        }

        return $apl;
    }
}
