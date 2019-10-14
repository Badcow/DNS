<?php

/*
 * This file is part of Badcow DNS Library.
 *
 * (c) Samuel Williams <sam@badcow.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Badcow\DNS\Rdata;

class NSEC implements RdataInterface
{
    use RdataTrait;

    const TYPE = 'NSEC';

    /**
     * The Next Domain field contains the next owner name (in the canonical
     * ordering of the zone) that has authoritative data or contains a
     * delegation point NS RRset.
     * {@link https://tools.ietf.org/html/rfc4034#section-4.1.1}.
     *
     * @var string
     */
    private $nextDomainName;

    /**
     * @var array
     */
    private $typeBitMaps = [];

    /**
     * @return string
     */
    public function getNextDomainName(): string
    {
        return $this->nextDomainName;
    }

    /**
     * @param string $nextDomainName
     */
    public function setNextDomainName(string $nextDomainName): void
    {
        $this->nextDomainName = $nextDomainName;
    }

    /**
     * @param string $type
     */
    public function addTypeBitMap(string $type): void
    {
        $this->typeBitMaps[] = $type;
    }

    /**
     * Clears the types from the RDATA.
     */
    public function clearTypeMap(): void
    {
        $this->typeBitMaps = [];
    }

    /**
     * @return array
     */
    public function getTypeBitMaps(): array
    {
        return $this->typeBitMaps;
    }

    /**
     * {@inheritdoc}
     */
    public function output(): string
    {
        return sprintf(
            '%s %s',
            $this->nextDomainName,
            implode(' ', $this->typeBitMaps)
        );
    }
}
