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

interface ResourceRecordInterface
{
    /**
     * Set the class for the resource record
     * Usually one of IN, HS, or CH.
     *
     * @param string $class
     */
    public function setClass($class);

    /**
     * @return string
     */
    public function getClass();

    /**
     * Set the name for the resource record.
     * Eg. "subdomain.example.com."
     *
     * @param $name
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param Rdata\RdataInterface $rdata
     */
    public function setRdata(Rdata\RdataInterface $rdata);

    /**
     * @return Rdata\RdataInterface
     */
    public function getRdata();

    /**
     * Set the time to live.
     *
     * @param int $ttl
     */
    public function setTtl($ttl);

    /**
     * @return int
     */
    public function getTtl();

    /**
     * Set a comment for the record
     *
     * @param $comment
     */
    public function setComment($comment);

    /**
     * Get the record's comment
     *
     * @return string
     */
    public function getComment();
}