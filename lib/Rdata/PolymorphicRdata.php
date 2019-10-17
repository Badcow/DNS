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

/**
 * Used to create RData of types that have not yet been implemented in the library.
 */
class PolymorphicRdata implements RdataInterface
{
    use RdataTrait;

    /**
     * The RData type.
     *
     * @var string|null
     */
    private $type;

    /**
     * @var string|null
     */
    private $data;

    /**
     * @var int|null
     */
    private $typeCode;

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
            $this->typeCode = TypeCodes::getTypeCode($type);
        } catch (\Exception $e) {
            $this->typeCode = 65535;
        }
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type ?? '';
    }

    public function getTypeCode(): int
    {
        return $this->getTypeCode();
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
     * @return string
     */
    public function toText(): string
    {
        return $this->getData() ?? '';
    }
}
