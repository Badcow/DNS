<?php

declare(strict_types=1);

/*
 * This file is part of Badcow DNS Library.
 *
 * (c) Samuel Williams <sam@badcow.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Badcow\DNS;

use Badcow\DNS\Rdata\A;
use Badcow\DNS\Rdata\Factory;
use Badcow\DNS\Rdata\RdataInterface;
use Badcow\DNS\Rdata\Types;
use InvalidArgumentException;

class ResourceRecord
{
    /**
     * @var int|null
     */
    private $classId = 1;

    /**
     * @var RdataInterface|null
     */
    private $rdata;

    /**
     * @var int|null
     */
    private $ttl;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string|null
     */
    private $comment;

    /**
     * @param string         $name
     * @param RdataInterface $rdata
     * @param int            $ttl
     * @param string         $class
     * @param string         $comment
     *
     * @return ResourceRecord
     */
    public static function create(string $name, RdataInterface $rdata, int $ttl = null, string $class = Classes::INTERNET, string $comment = null): ResourceRecord
    {
        $rr = new self();
        $rr->setName($name);
        $rr->setRdata($rdata);
        $rr->setTtl($ttl);
        $rr->setClass($class);
        $rr->setComment($comment);

        return $rr;
    }

    /**
     * Set the class for the resource record
     * Usually one of IN, HS, or CH.
     *
     * @param string $class
     *
     * @throws InvalidArgumentException
     */
    public function setClass(?string $class): void
    {
        if (null !== $class && !Classes::isValid($class)) {
            throw new InvalidArgumentException(sprintf('No such class as "%s"', $class));
        }

        if (null === $class) {
            $this->classId = null;

            return;
        }

        $this->classId = Classes::getClassId($class);
    }

    /**
     * Set the name for the resource record.
     * Eg. "subdomain.example.com.".
     *
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @param RdataInterface $rdata
     */
    public function setRdata(?RdataInterface $rdata): void
    {
        $this->rdata = $rdata;
    }

    /**
     * @return string
     */
    public function getClass(): ?string
    {
        if (null === $this->classId) {
            return null;
        }

        return Classes::getClassName($this->classId);
    }

    public function setClassId(int $classId): void
    {
        $this->classId = $classId;
    }

    /**
     * @return int|null
     */
    public function getClassId(): ?int
    {
        return $this->classId;
    }

    /**
     * Set the time to live.
     *
     * @param int $ttl
     */
    public function setTtl(?int $ttl): void
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
    public function setComment(?string $comment): void
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

    /**
     * @return string
     *
     * @throws UnsetValueException|InvalidArgumentException
     */
    public function toWire(): string
    {
        if (null === $this->name) {
            throw new UnsetValueException('ResourceRecord name has not been set.');
        }

        if (null === $this->rdata) {
            throw new UnsetValueException('ResourceRecord rdata has not been set.');
        }

        if (null === $this->classId) {
            throw new UnsetValueException('ResourceRecord class has not been set.');
        }

        if (null === $this->ttl) {
            throw new UnsetValueException('ResourceRecord TTL has not been set.');
        }

        if (!Validator::fullyQualifiedDomainName($this->name)) {
            throw new InvalidArgumentException(sprintf('"%s" is not a fully qualified domain name.', $this->name));
        }

        $rdata = $this->rdata->toWire();

        $encoded = A::encodeName($this->name);
        $encoded .= pack('nnNn',
            $this->rdata->getTypeCode(),
            $this->classId,
            $this->ttl,
            strlen($rdata)
        );
        $encoded .= $rdata;

        return $encoded;
    }

    /**
     * @param string $encoded
     * @param int    $offset
     *
     * @return ResourceRecord
     *
     * @throws Rdata\UnsupportedTypeException
     */
    public static function fromWire(string $encoded, int &$offset = 0): ResourceRecord
    {
        $rr = new self();
        $rr->setName(A::decodeName($encoded, $offset));
        $integers = unpack('ntype/nclass/Nttl/ndlength', $encoded, $offset);
        $offset += 10;
        $rr->setClassId($integers['class']);
        $rr->setTtl($integers['ttl']);
        $rdLength = $integers['dlength'];

        /** @var callable $callable */
        $callable = Factory::getRdataClassName(Types::getName($integers['type'])).'::fromWire';
        /** @var RdataInterface $rdata */
        $rdata = $callable($encoded, $offset, $rdLength);

        $rr->setRdata($rdata);

        return $rr;
    }
}
