<?php
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
 * Class ZoneFile
 *
 * This is likely to be broken up and put under a
 * different file.
 *
 * @package Badcow\DNS
 */
class ZoneFile extends ZoneBuilder
{
    /**
     * @var ZoneInterface
     */
    private $zone;

    /**
     * @param ZoneInterface $zone
     */
    public function __construct(ZoneInterface $zone = null)
    {
        if (null !== $zone) {
            $this->setZone($zone);
        }
    }

    /**
     * @param ZoneInterface $zone
     * @throws ZoneException
     */
    public function setZone(ZoneInterface $zone)
    {
        $this->validate($zone);

        if (null === $zone->getZoneName()) {
            throw new ZoneException('No zone name is specified in the zone class.');
        }

        if (null === $zone->getDefaultTtl()) {
            throw new ZoneException('No TTL is specified in the zone class.');
        }

        $this->zone = $zone;
    }

    /**
     * @return ZoneInterface
     */
    public function getZone()
    {
        return $this->zone;
    }

    /**
     * Validates that the zone meets
     * RFC-1035 especially that:
     *   1) 5.2.1 All RRs in the file should have the same class.
     *   2) 5.2.2 Exactly one SOA RR should be present at the top of the zone.
     *
     * @param ZoneInterface $zone
     * @throws ZoneException
     */
    public function validate(ZoneInterface $zone)
    {
        $number_soa = 0;
        $number_ns = 0;
        $classes = array();

        foreach ($zone->getResourceRecords() as $rr) {
            /* @var $rr ResourceRecordInterface */
            if ('SOA' === $rr->getRdata()->getType()) {
                $number_soa += 1;
            }

            if ('NS' === $rr->getRdata()->getType()) {
                $number_ns += 1;
            }

            if (null !== $rr->getClass()) {
                $classes[$rr->getClass()] = '';
            }
        }

        if ($number_soa !== 1) {
            throw new ZoneException(sprintf('There must be exactly one SOA record, %s given.', $number_soa));
        }

        if ($number_ns < 1) {
            throw new ZoneException(sprintf('There must be at least one NS record, %s given.', $number_ns));
        }

        if (1 !== $c = count($classes)) {
            throw new ZoneException(sprintf('There must be exactly one type of class, %s given.', $c));
        }
    }

    /**
     * @return string
     */
    public function render()
    {
        return $this->build($this->zone);
    }

    /**
     * Save the zone to a directory
     *
     * @param string $directory Directory of where to store the file
     * @return bool
     */
    public function saveToFile($directory)
    {
        $filename = sprintf('%s' . DIRECTORY_SEPARATOR . '%s', $directory, substr($this->zone->getZoneName(), 0, -1));

        return file_put_contents($filename, $this->render()) !== false;
    }
}
