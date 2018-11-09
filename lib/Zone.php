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

use Badcow\DNS\Ip\Toolbox;
use Badcow\DNS\Rdata\AAAA;
use Badcow\DNS\Rdata\CNAME;
use Badcow\DNS\Rdata\MX;

class Zone implements ZoneInterface
{
    use ZoneTrait;

    /**
     * Zone constructor.
     *
     * @param string $name
     * @param int    $defaultTtl
     * @param array  $resourceRecords
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($name = null, $defaultTtl = null, array $resourceRecords = [])
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
     * @throws \InvalidArgumentException
     */
    public function setName($name)
    {
        if (!Validator::rrName($name, true)) {
            throw new \InvalidArgumentException(sprintf('Zone "%s" is not a fully qualified domain name.', $name));
        }

        $this->name = $name;
    }

    /**
     * Fills out all of the data of each resource record.
     */
    public function expand()
    {
        $class = $this->determineClass();

        foreach ($this->resourceRecords as &$rr) {
            /** @var ResourceRecord $rr */
            if ('@' === $rr->getName()) {
                $rr->setName($this->name);
            }

            if (!Validator::fqdn($rr->getName())) {
                $rr->setName($rr->getName().'.'.$this->name);
            }

            if (null === $rr->getTtl()) {
                $rr->setTtl($this->getDefaultTtl());
            }

            if ($rr->getRdata() instanceof CNAME) {
                if ('@' === $rr->getRdata()->getTarget()) {
                    $rr->getRdata()->setTarget($this->name);
                }

                if (!Validator::fqdn($rr->getRdata()->getTarget())) {
                    $rr->getRdata()->setTarget($rr->getRdata()->getTarget().'.'.$this->name);
                }
            }

            if ($rr->getRdata() instanceof MX) {
                if ('@' === $rr->getRdata()->getExchange()) {
                    $rr->getRdata()->setExchange($this->name);
                }

                if (!Validator::fqdn($rr->getRdata()->getExchange())) {
                    $rr->getRdata()->setExchange($rr->getRdata()->getExchange().'.'.$this->name);
                }
            }

            if ($rr->getRdata() instanceof AAAA) {
                $rr->getRdata()->setAddress(Toolbox::expandIpv6($rr->getRdata()->getAddress()));
            }

            $rr->setClass($class);
        }
    }

    /**
     * Determine in which class the zone resides. Returns `IN` as the default.
     *
     * @return string
     */
    private function determineClass()
    {
        foreach ($this->resourceRecords as $rr) {
            if (null !== $rr->getClass()) {
                return $rr->getClass();
            }
        }

        return Classes::INTERNET;
    }
}
