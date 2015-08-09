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

interface RdataRegistrableInterface
{
    /**
     * Indicates that an object can be injected with Rdata types. Useful when additional types beyond the scope
     * of the original library are required.
     *
     * @param string $type The type of rdata (should be uppercase eg: "DNAME", not "dname")
     * @param string $fqcn The fully qualified class name of the Rdata type
     *
     * @throws \InvalidArgumentException
     */
    public function registerRdataType($type, $fqcn);

    /**
     * Returns true if the Rdata type has been registered.
     *
     * @param string $type
     *
     * @return bool
     */
    public function hasRdataType($type);
}
