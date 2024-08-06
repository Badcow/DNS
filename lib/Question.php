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

use Badcow\DNS\Rdata\DecodeException;
use Badcow\DNS\Rdata\Types;
use Badcow\DNS\Rdata\UnsupportedTypeException;
use InvalidArgumentException;
use UnexpectedValueException;

class Question
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $typeCode;

    /**
     * @var int
     */
    private $classId;

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @throws InvalidArgumentException
     */
    public function setName($name): void
    {
        if (!Validator::fullyQualifiedDomainName($name, false)) {
            throw new InvalidArgumentException(sprintf('"%s" is not a fully qualified domain name.', $name));
        }

        $this->name = $name;
    }

    public function getTypeCode(): int
    {
        return $this->typeCode;
    }

    /**
     * @throws UnsupportedTypeException
     */
    public function getType(): string
    {
        return Types::getName($this->typeCode);
    }

    /**
     * @throws \DomainException
     */
    public function setTypeCode(int $typeCode): void
    {
        if (!Validator::isUnsignedInteger($typeCode, 16)) {
            throw new \DomainException(sprintf('TypeCode must be an unsigned 16-bit integer. "%d" given.', $typeCode));
        }
        $this->typeCode = $typeCode;
    }

    /**
     * @throws UnsupportedTypeException
     */
    public function setType(string $type): void
    {
        $this->setTypeCode(Types::getTypeCode($type));
    }

    public function getClassId(): int
    {
        return $this->classId;
    }

    public function getClass(): string
    {
        return Classes::getClassName($this->classId);
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function setClassId(int $classId): void
    {
        if (!Validator::isUnsignedInteger($classId, 16)) {
            throw new InvalidArgumentException(sprintf('Invalid class: "%s".', $classId));
        }

        $this->classId = $classId;
    }

    public function setClass(string $class): void
    {
        $this->setClassId(Classes::getClassId($class));
    }

    public function toWire(): string
    {
        return Message::encodeName($this->name).pack('nn', $this->typeCode, $this->classId);
    }

    /**
     * @throws UnexpectedValueException
     * @throws UnsupportedTypeException
     */
    public static function fromWire(string $encoded, int &$offset = 0): Question
    {
        $question = new self();
        $question->setName(Message::decodeName($encoded, $offset));
        if (false === $integers = unpack('ntype/nclass', $encoded, $offset)) {
            throw new \UnexpectedValueException(sprintf('Malformed DNS query encountered. "%s"', DecodeException::binaryToHex($encoded)));
        }
        $question->setTypeCode($integers['type']);
        $question->setClassId($integers['class']);
        $offset += 4;

        return $question;
    }
}
