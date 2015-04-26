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

interface ZoneInterface
{
    /**
     * @param ResourceRecordInterface[] $resourceRecord
     */
    public function setResourceRecords(array $resourceRecord);

    /**
     * @param ResourceRecordInterface $resourceRecord
     */
    public function addResourceRecord(ResourceRecordInterface $resourceRecord);

    /**
     * @return ResourceRecordInterface[]
     */
    public function getResourceRecords();

    /**
     * @param int $defaultTtl
     */
    public function setDefaultTtl($defaultTtl);

    /**
     * @return int
     */
    public function getDefaultTtl();

    /**
     * @deprecated Use Zone::getName() instead
     * @param string $zone A fully qualified zone name
     */
    public function setZoneName($zone);

    /**
     * @deprecated Use Zone::getName() instead
     * @return string
     */
    public function getZoneName();

    /**
     * @param string $zone A fully qualified zone name
     */
    public function setName($zone);

    /**
     * @return string
     */
    public function getName();
}
