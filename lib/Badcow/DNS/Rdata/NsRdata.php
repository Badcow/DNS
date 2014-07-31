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

use Badcow\DNS\Validator;

class NsRdata implements RdataInterface
{
    const TYPE = "NS";

    /**
     * @var string
     */
    private $nsdname;

    /**
     * @param $nsdname
     * @return NsRdata
     * @throws RdataException
     */
    public function setNsdname($nsdname)
    {
        if (!Validator::validateFqdn($nsdname)) {
            throw new RdataException(sprintf('Domain name "%s" is not a Fully Qualified Domain Name', $nsdname));
        }

        $this->nsdname = $nsdname;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLength()
    {
        return strlen($this->nsdname);
    }

    /**
     * {@inheritdoc}
     */
    public function output()
    {
        return $this->nsdname;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE;
    }
}