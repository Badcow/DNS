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

use Badcow\DNS\Rdata\RdataInterface;

trait RdataRegisterTrait
{
    protected $rdataTypes = [
        Rdata\CNAME::TYPE => '\\Badcow\\DNS\\Rdata\\CnameRdata',
        Rdata\DNAME::TYPE => '\\Badcow\\DNS\\Rdata\\DnameRdata',
        Rdata\HINFO::TYPE => '\\Badcow\\DNS\\Rdata\\HinfoRdata',
        Rdata\AAAA::TYPE => '\\Badcow\\DNS\\Rdata\\AaaaRdata',
        Rdata\SOA::TYPE => '\\Badcow\\DNS\\Rdata\\SoaRdata',
        Rdata\LOC::TYPE => '\\Badcow\\DNS\\Rdata\\LocRdata',
        Rdata\PTR::TYPE => '\\Badcow\\DNS\\Rdata\\PtrRdata',
        Rdata\TXT::TYPE => '\\Badcow\\DNS\\Rdata\\TxtRdata',
        Rdata\NS::TYPE => '\\Badcow\\DNS\\Rdata\\NsRdata',
        Rdata\MX::TYPE => '\\Badcow\\DNS\\Rdata\\MxRdata',
        Rdata\A::TYPE => '\\Badcow\\DNS\\Rdata\\ARdata',
    ];

    /**
     * @param string $type
     * @param string $fqcn
     *
     * @throws \InvalidArgumentException
     */
    public function registerRdataType($type, $fqcn)
    {
        if (false === (new \ReflectionClass($fqcn))->implementsInterface('\\Badcow\\DNS\\Rdata\\RdataInterface')) {
            throw new \InvalidArgumentException(sprintf(
                'The class "%s" is not an instance of Badcow\DNS\Rdata\RdataInterface',
                $fqcn
            ));
        }

        $this->rdataTypes[$type] = $fqcn;
    }

    /**
     * Removes an Rdata type.
     *
     * @param $type
     */
    public function removeRdataType($type)
    {
        if (!$this->hasRdataType($type)) {
            return;
        }

        unset($this->rdataTypes[$type]);
    }

    /**
     * @param $type
     *
     * @return bool
     */
    public function hasRdataType($type)
    {
        return array_key_exists($type, $this->rdataTypes);
    }

    /**
     * @return array
     */
    public function getRegisteredTypes()
    {
        return array_keys($this->rdataTypes);
    }

    /**
     * Returns an Rdata instance based on the type.
     *
     * @param $type
     *
     * @return RdataInterface
     *
     * @throws \DomainException
     */
    protected function getNewRdataByType($type)
    {
        if (!$this->hasRdataType($type)) {
            throw new \DomainException(sprintf(
                'The Rdata type "%s" is not a registered type.',
                $type
            ));
        }

        return new $this->rdataTypes[$type]();
    }
}
