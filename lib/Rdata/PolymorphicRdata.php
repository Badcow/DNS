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

namespace Badcow\DNS\Rdata;

/**
 * Used to create RData of types that have not yet been implemented in the library.
 */
class PolymorphicRdata implements RdataInterface
{
    /**
     * The RData type.
     *
     * @var string
     */
    private $type;

    /**
     * @var string|null
     */
    private $data;

    /**
     * @var int
     */
    private $typeCode = 0;

    /**
     * PolymorphicRdata constructor.
     *
     * @param string|null $type
     * @param string|null $data
     */
    public function __construct(?string $type = null, ?string $data = null)
    {
        if (null !== $type) {
            $this->setType($type);
        }

        if (null !== $data) {
            $this->setData($data);
        }
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        try {
            $this->typeCode = Types::getTypeCode($type);
        } catch (UnsupportedTypeException $e) {
            $this->typeCode = 0;
        }
        $this->type = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param int $typeCode
     */
    public function setTypeCode(int $typeCode): void
    {
        $this->typeCode = $typeCode;
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeCode(): int
    {
        return $this->typeCode;
    }

    /**
     * @param string $data
     */
    public function setData(string $data): void
    {
        $this->data = $data;
    }

    /**
     * @return string|null
     */
    public function getData(): ?string
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function toText(): string
    {
        return $this->getData() ?? '';
    }

    /**
     * {@inheritdoc}
     */
    public function toWire(): string
    {
        return $this->data ?? '';
    }

    /**
     * {@inheritdoc}
     */
    public static function fromText(string $text): RdataInterface
    {
        return new self(null, $text);
    }

    /**
     * {@inheritdoc}
     */
    public static function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): RdataInterface
    {
        return new self(null, substr($rdata, $offset, $rdLength ?? strlen($rdata)));
    }
}
