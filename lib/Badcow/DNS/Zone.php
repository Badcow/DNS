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

use Badcow\DNS\Validator;

class Zone implements ZoneInterface
{
    /**
     * @var string
     */
    private $zoneName;

    /**
     * @var array an array of ResourceRecord
     */
    private $resourceRecords;

    /**
     * @var int
     */
    private $defaultTtl;

    /**
     * @param string $zoneName
     * @param string $defaultTtl
     * @param ResourceRecordInterface[] $resourceRecords
     */
    public function __construct($zoneName = null, $defaultTtl = null, array $resourceRecords = array())
    {
        $this->setZoneName($zoneName);
        $this->setDefaultTtl($defaultTtl);
        $this->setResourceRecords($resourceRecords);
    }

    /**
     * @param ResourceRecordInterface[] $resourceRecord
     */
    public function setResourceRecords(array $resourceRecord)
    {
        foreach ($resourceRecord as $rr) {
            /** @var ResourceRecordInterface $rr */
            $this->addResourceRecord($rr);
        }
    }

    /**
     * @param ResourceRecordInterface $resourceRecord
     */
    public function addResourceRecord(ResourceRecordInterface $resourceRecord)
    {
        $this->resourceRecords[] = $resourceRecord;
    }

    /**
     * @return ResourceRecordInterface[]
     */
    public function getResourceRecords()
    {
        return $this->resourceRecords;
    }

    /**
     * @param int $defaultTtl
     */
    public function setDefaultTtl($defaultTtl)
    {
        $this->defaultTtl = (int) $defaultTtl;
    }

    /**
     * @return int
     */
    public function getDefaultTtl()
    {
        return $this->defaultTtl;
    }

    /**
     * @param string $zone A fully qualified zone name
     * @throws ZoneException
     */
    public function setZoneName($zone)
    {
        if (!Validator::validateFqdn($zone)) {
            throw new ZoneException(sprintf('Zone "%s" is not a fully qualified domain name.', $zone));
        }

        $this->zoneName = $zone;
    }

    /**
     * @return string
     */
    public function getZoneName()
    {
        return $this->zoneName;
    }
}