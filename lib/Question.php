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

use Badcow\DNS\Rdata\RdataTrait;
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

    /**
     * @return string
     */
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
        if (!Validator::fullyQualifiedDomainName($name)) {
            throw new InvalidArgumentException(sprintf('"%s" is not a fully qualified domain name.', $name));
        }

        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getTypeCode(): int
    {
        return $this->typeCode;
    }

    /**
     * @return string
     *
     * @throws UnsupportedTypeException
     */
    public function getType(): string
    {
        return Types::getName($this->typeCode);
    }

    /**
     * @param string|int $typeCode
     *
     * @throws UnsupportedTypeException
     */
    public function setTypeCode($typeCode): void
    {
        if (is_string($typeCode)) {
            $this->typeCode = Types::getTypeCode($typeCode);
        } elseif (is_int($typeCode)) {
            $this->typeCode = $typeCode;
        } else {
            throw new UnsupportedTypeException(sprintf('Library does not support type "%s".', $typeCode));
        }
    }

    /**
     * @param string $type
     *
     * @throws UnsupportedTypeException
     */
    public function setType(string $type): void
    {
        $this->setTypeCode(Types::getTypeCode($type));
    }

    /**
     * @return int
     */
    public function getClassId(): int
    {
        return $this->classId;
    }

    /**
     * @param string|int $classId
     */
    public function setClassId($classId): void
    {
        if (is_string($classId)) {
            $this->classId = Classes::getClassId($classId);
        } elseif (Validator::isUnsignedInteger($classId, 16)) {
            $this->classId = $classId;
        } else {
            throw new InvalidArgumentException(sprintf('Invalid class: "%s".', $classId));
        }
    }

    /**
     * @return string
     */
    public function toWire(): string
    {
        return RdataTrait::encodeName($this->name).pack('nn', $this->typeCode, $this->classId);
    }

    /**
     * @param string $encoded
     * @param int    $offset
     *
     * @return Question
     *
     * @throws UnexpectedValueException
     * @throws UnsupportedTypeException
     */
    public static function fromWire(string $encoded, int &$offset = 0): Question
    {
        $question = new self();
        $question->setName(RdataTrait::decodeName($encoded, $offset));
        $integers = unpack('ntype/nclass', $encoded, $offset);
        $question->setTypeCode($integers['type']);
        $question->setClassId($integers['class']);
        $offset += 4;

        return $question;
    }
}
