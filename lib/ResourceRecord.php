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

class ResourceRecord
{
    /**
     * @var string
     */
    private $class = Classes::INTERNET;

    /**
     * @var RdataInterface
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
     * @param string         $name
     * @param RdataInterface $rdata
     * @param int            $ttl
     * @param string         $class
     * @param string         $comment
     */
    public function __construct(string $name = null, RdataInterface $rdata = null, int $ttl = null, string $class = null, string $comment = null)
    {
        $this->name = $name;
        $this->rdata = $rdata;
        $this->ttl = $ttl;
        $this->setClass($class);
        $this->rdata = $rdata;
        $this->comment = $comment;
    }

    /**
     * Set the class for the resource record
     * Usually one of IN, HS, or CH.
     *
     * @param string $class
     *
     * @throws \UnexpectedValueException
     */
    public function setClass(?string $class)
    {
        if (null !== $class && !Classes::isValid($class)) {
            throw new \UnexpectedValueException(sprintf('No such class as "%s"', $class));
        }

        $this->class = $class;
    }

    /**
     * Set the name for the resource record.
     * Eg. "subdomain.example.com.".
     *
     * @param $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @param RdataInterface $rdata
     */
    public function setRdata(RdataInterface $rdata): void
    {
        $this->rdata = $rdata;
    }

    /**
     * @return string
     */
    public function getClass(): ?string
    {
        return $this->class;
    }

    /**
     * Set the time to live.
     *
     * @param int $ttl
     */
    public function setTtl(int $ttl)
    {
        $this->ttl = $ttl;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getType(): ?string
    {
        if (null === $this->rdata) {
            return null;
        }

        return $this->rdata->getType();
    }

    /**
     * @return RdataInterface
     */
    public function getRdata(): ?RdataInterface
    {
        return $this->rdata;
    }

    /**
     * @return int
     */
    public function getTtl(): ?int
    {
        return $this->ttl;
    }

    /**
     * Set a comment for the record.
     *
     * @param string $comment
     */
    public function setComment(string $comment): void
    {
        $this->comment = $comment;
    }

    /**
     * Get the record's comment.
     *
     * @return string
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }
}
