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

trait ZoneTrait
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var ResourceRecord[]
     */
    private $resourceRecords = array();

    /**
     * @var int
     */
    private $defaultTtl;

    /**
     * @var array
     */
    private $ctrlEntries = [];

    /**
     * @param ResourceRecordInterface[] $resourceRecord
     */
    public function setResourceRecords(array $resourceRecord)
    {
        foreach ($resourceRecord as $rr) {
            /* @var ResourceRecordInterface $rr */
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
     * @param string $name A fully qualified zone name
     *
     * @throws ZoneException
     */
    public function setName($name)
    {
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
     * @param string $name
     * @param string $value
     */
    public function addControlEntry($name, $value)
    {
        $this->ctrlEntries[] = ['name' => $name, 'value' => $value];
    }

    /**
     * @param string $name
     *
     * @return array
     */
    public function getControlEntry($name)
    {
        $out = [];
        foreach ($this->ctrlEntries as $entry) {
            if ($name === $entry['name']) {
                $out[] = $entry['value'];
            }
        }

        return $out;
    }

    /**
     * @return array
     */
    public function getControlEntries()
    {
        return $this->ctrlEntries;
    }
}
