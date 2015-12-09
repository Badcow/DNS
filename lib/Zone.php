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
    use ZoneTrait;

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
     * @param string $name A fully qualified zone name
     *
     * @throws ZoneException
     */
    public function setName($name)
    {
        if (!Validator::rrName($name, true)) {
            throw new ZoneException(sprintf('Zone "%s" is not a fully qualified domain name.', $name));
        }

        $this->name = $name;
    }
}
