<?php
/*
 * This file is part of Badcow DNS Library.
 *
 * (c) Samuel Williams <sam@badcow.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Badcow\DNS\Rdata;

class NsRdata extends CnameRdata
{
    const TYPE = 'NS';

    /**
     * @deprecated Use NsRdata::setTarget() instead
     * @codeCoverageIgnore
     * @param $nsdname
     * @throws RdataException
     */
    public function setNsdname($nsdname)
    {
        $this->setTarget($nsdname);
    }

    /**
     * @deprecated Use NsRdata::getTarget() instead
     * @codeCoverageIgnore
     * @return string
     */
    public function getNsdname()
    {
        return $this->getTarget();
    }
}
