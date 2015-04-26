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

class Zone implements ZoneInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array an array of ResourceRecord
     */
    private $resourceRecords;

    /**
     * @var int
     */
    private $defaultTtl;

    /**
     * @param string                    $name
     * @param string                    $defaultTtl
     * @param ResourceRecordInterface[] $resourceRecords
     */
    public function __construct($name = null, $defaultTtl = null, array $resourceRecords = array())
    {
        if (null !== $name) {
            $this->setName($name);
        }

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
     * @param  string        $name A fully qualified zone name
     * @throws ZoneException
     */
    public function setName($name)
    {
        if (!Validator::validateFqdn($name)) {
            throw new ZoneException(sprintf('Zone "%s" is not a fully qualified domain name.', $name));
        }

        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @deprecated Use Zone::getName() instead
     * @param  string        $zone A fully qualified zone name
     * @throws ZoneException
     */
    public function setZoneName($zone)
    {
        $this->setName($zone);
    }

    /**
     * @deprecated Use Zone::getName() instead
     * @return string
     */
    public function getZoneName()
    {
        return $this->getName();
    }
}
