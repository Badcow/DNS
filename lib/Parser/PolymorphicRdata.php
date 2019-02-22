<?php

/*
 * This file is part of Badcow DNS Library.
 *
 * (c) Samuel Williams <sam@badcow.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Badcow\DNS\Parser;

use Badcow\DNS\Rdata\RdataInterface;

class PolymorphicRdata implements RdataInterface
{
    /**
     * The RData type.
     *
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $data;

    /**
     * PolymorphicRdata constructor.
     *
     * @param string|null $type
     * @param string|null $data
     */
    public function __construct(string $type = null, string $data = null)
    {
        $this->setType($type);
        $this->setData($data);
    }

    /**
     * @param string $type
     */
    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $data
     */
    public function setData(?string $data): void
    {
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getData(): string
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function output(): string
    {
        return $this->getData();
    }
}
