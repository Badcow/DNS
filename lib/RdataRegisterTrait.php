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
        Rdata\CnameRdata::TYPE => '\\Badcow\\DNS\\Rdata\\CnameRdata',
        Rdata\DnameRdata::TYPE => '\\Badcow\\DNS\\Rdata\\DnameRdata',
        Rdata\HinfoRdata::TYPE => '\\Badcow\\DNS\\Rdata\\HinfoRdata',
        Rdata\AaaaRdata::TYPE => '\\Badcow\\DNS\\Rdata\\AaaaRdata',
        Rdata\SoaRdata::TYPE => '\\Badcow\\DNS\\Rdata\\SoaRdata',
        Rdata\LocRdata::TYPE => '\\Badcow\\DNS\\Rdata\\LocRdata',
        Rdata\PtrRdata::TYPE => '\\Badcow\\DNS\\Rdata\\PtrRdata',
        Rdata\TxtRdata::TYPE => '\\Badcow\\DNS\\Rdata\\TxtRdata',
        Rdata\NsRdata::TYPE => '\\Badcow\\DNS\\Rdata\\NsRdata',
        Rdata\MxRdata::TYPE => '\\Badcow\\DNS\\Rdata\\MxRdata',
        Rdata\ARdata::TYPE => '\\Badcow\\DNS\\Rdata\\ARdata',
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
