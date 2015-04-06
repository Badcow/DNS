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

class CnameRdata implements RdataInterface
{
    use RdataTrait;

    const TYPE = 'CNAME';

    /**
     * @var string
     */
    private $cname;

    /**
     * @param $cname
     * @throws RdataException
     */
    public function setCname($cname)
    {
        if (!Validator::validateFqdn($cname)) {
            throw new RdataException('Cname is not a Fully Qualified Domain Name');
        }

        $this->cname = $cname;
    }

    /**
     * @return string
     */
    public function getCname()
    {
        return $this->cname;
    }

    /**
     * {@inheritdoc}
     */
    public function output()
    {
        return $this->cname;
    }
}