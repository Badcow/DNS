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
    private $type;

    /**
     * @var int
     */
    private $class;

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
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param string|int $type
     *
     * @throws UnsupportedTypeException
     */
    public function setType($type): void
    {
        if (is_string($type)) {
            $this->type = Types::getTypeCode($type);
        } elseif (is_int($type)) {
            $this->type = $type;
        } else {
            throw new UnsupportedTypeException(sprintf('Library does not support type "%s".', $type));
        }
    }

    /**
     * @return int
     */
    public function getClass(): int
    {
        return $this->class;
    }

    /**
     * @param string|int $class
     */
    public function setClass($class): void
    {
        if (is_string($class)) {
            $this->class = Classes::getClassId($class);
        } elseif (Validator::isUnsignedInteger($class, 16)) {
            $this->class = $class;
        } else {
            throw new InvalidArgumentException(sprintf('Invalid class: "%s".', $class));
        }
    }

    /**
     * @return string
     */
    public function toWire(): string
    {
        return RdataTrait::encodeName($this->name).pack('nn', $this->type, $this->class);
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
        $question->setType($integers['type']);
        $question->setClass($integers['class']);
        $offset += 4;

        return $question;
    }
}
