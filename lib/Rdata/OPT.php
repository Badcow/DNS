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

use Badcow\DNS\Edns\Option\Factory;
use Badcow\DNS\Edns\Option\UnknownOption;
use Badcow\DNS\Edns\Option\UnsupportedOptionException;

/**
 * @see https://datatracker.ietf.org/doc/html/rfc6891#section-6.1.3
 */
class OPT implements RdataInterface
{
    use RdataTrait;

    public const TYPE = 'OPT';
    public const TYPE_CODE = 41;

    /**
     * @var EdnsOption[]|null
     */
    protected $options;

    public function toText(): string
    {
        return '';
    }

    public function fromText(string $text): void
    {
        throw new \Exception();
    }

    /**
     * @var EdnsOption[]|null
     */
    public function setOptions(?array $options): void
    {
        $this->options = $options;
    }

    /**
     * @return EdnsOption[]
     */
    public function getOptions(): ?array
    {
        return $this->options;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function toWire(): string
    {
        $encoded = '';
        if (!$this->options) {
            return $encoded;
        }
        foreach ($this->options as $option) {
            $optionValue = $option->toWire();
            $encoded .= pack('nn', $option->getNameCode(), strlen($optionValue));
            $encoded .= $optionValue;
        }

        return $encoded;
    }

    public function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): void
    {
        if (null === $rdLength) {
            $rdLength = strlen($rdata);
        }
        $endOffset = $offset + $rdLength;
        do {
            if (false === $integers = unpack('ncode/nlength', $rdata, $offset)) {
                throw new \UnexpectedValueException(sprintf('Malformed resource record encountered. "%s"', DecodeException::binaryToHex($encoded)));
            }
            $offset += 4;
            try {
                $option = Factory::newOptionFromId($integers['code']);
            } catch (UnsupportedOptionException $e) {
                $option = new UnknownOption();
                $option->setOptionCode($integers['code']);
            }
            $option->fromWire($rdata, $offset, $integers['length']);
            $this->options[] = $option;
        } while ($offset < $endOffset);
    }
}