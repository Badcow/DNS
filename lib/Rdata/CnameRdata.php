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
    protected $target;

    /**
     * @param $target
     *
     * @throws RdataException
     */
    public function setTarget($target)
    {
        if (!Validator::validateFqdn($target, false)) {
            throw new RdataException(sprintf('The target "%s" is not a Fully Qualified Domain Name', $target));
        }

        $this->target = $target;
    }

    /**
     * @return string
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * {@inheritdoc}
     */
    public function output()
    {
        return $this->target;
    }
}
