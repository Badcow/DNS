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

namespace Badcow\DNS\Edns\Option;

class UnknownOption implements OptionInterface
{
    /**
     * @var int
     */
    private $optionCode;

    /**
     * @var string|null
     */
    private $data;

    /**
     * @var string|null
     */
    private $name;

    public function setOptionCode(int $optionCode): void
    {
        $this->optionCode = $optionCode;
    }

    public function getNameCode(): int
    {
        return $this->optionCode;
    }

    public function getName(): string
    {
        if (null !== $this->name) {
            return $this->name;
        }

        return 'OPTION'.$this->optionCode;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getData(): ?string
    {
        return $this->data;
    }

    /**
     * @param string $data
     */
    public function setData(?string $data): void
    {
        $this->data = $data;
    }

    public function getCode(): int
    {
        return $this->optionCode;
    }

    public function toWire(): string
    {
        return $this->data ?? '';
    }

    public function fromWire(string $optionValue, int &$offset = 0, ?int $optionLength = null): void
    {
        $optionLength = $optionLength ?? strlen($optionValue);
        $this->setData(substr($optionValue, $offset, $optionLength));
        $offset += $optionLength;
    }
}
