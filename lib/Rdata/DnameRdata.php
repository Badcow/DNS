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

/**
 * Class DnameRdata
 *
 * The DNAME record provides redirection for a subtree of the domain
 * name tree in the DNS.  That is, all names that end with a particular
 * suffix are redirected to another part of the DNS.
 * Based on RFC6672
 * @link http://tools.ietf.org/html/rfc6672
 *
 * @package Badcow\DNS\Rdata
 */
class DnameRdata implements RdataInterface
{
    use RdataTrait;

    const TYPE = 'DNAME';

    /**
     * @var string
     */
    private $target;

    /**
     * @param $target
     * @throws RdataException
     */
    public function setTarget($target)
    {
        if (!Validator::validateFqdn($target)) {
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