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

use Badcow\DNS\Validator;
use Badcow\DNS\Classes;

class ResourceRecord implements ResourceRecordInterface
{
    /**
     * @var string
     */
    private $class = Classes::INTERNET;

    /**
     * @var Rdata\RdataInterface
     */
    private $rdata;

    /**
     * @var int
     */
    private $ttl;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $comment;

    /**
     * @param string $class
     * @throws ResourceRecordException
     */
    public function setClass($class)
    {
        if (!array_key_exists($class, Classes::$classes)) {
            throw new ResourceRecordException(sprintf('No such class as "%s"', $class));
        }

        $this->class = $class;
    }

    /**
     * @param $name
     * @throws DNSException
     */
    public function setName($name)
    {
        if (!Validator::validateFqdn($name, false)) {
            throw new DNSException('The name is not a Fully Qualified Domain Name');
        }

        $this->name = (string) $name;
    }

    /**
     * @param Rdata\RdataInterface $rdata
     */
    public function setRdata(Rdata\RdataInterface $rdata)
    {
        $this->rdata = $rdata;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param int $ttl
     */
    public function setTtl($ttl)
    {
        $this->ttl = (int) $ttl;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->rdata->getType();
    }

    /**
     * @return Rdata\RdataInterface
     */
    public function getRdata()
    {
        return $this->rdata;
    }

    /**
     * @return int
     */
    public function getTtl()
    {
        return $this->ttl;
    }

    /**
     * Set a comment for the record
     *
     * @param string $comment
     */
    public function setComment($comment)
    {
        $comment = preg_replace('/(?:\n|\r)/', '', $comment);
        $this->comment = $comment;
    }

    /**
     * Get the record's comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }
}