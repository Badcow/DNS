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

use Badcow\DNS\Parser\Tokens;
use Badcow\DNS\Validator;

/**
 * {@link https://tools.ietf.org/html/rfc7553}.
 */
class URI implements RdataInterface
{
    use RdataTrait;

    public const TYPE = 'URI';
    public const TYPE_CODE = 256;

    /**
     * This field holds the priority of the target URI in this RR.  Its
     * range is 0-65535.  A client MUST attempt to contact the URI with the
     * lowest-numbered priority it can reach; URIs with the same priority
     * SHOULD be selected according to probabilities defined by the weight
     * field.
     *
     * @var int
     */
    private $priority;

    /**
     * This field holds the server selection mechanism.  The weight field
     * specifies a relative weight for entries with the same priority.
     * Larger weights SHOULD be given a proportionately higher probability
     * of being selected.  The range of this number is 0-65535.
     *
     * @var int
     */
    private $weight;

    /**
     * @var string
     */
    private $target;

    /**
     * @return int
     */
    public function getPriority(): ?int
    {
        return $this->priority;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function setPriority(int $priority): void
    {
        if (!Validator::isUnsignedInteger($priority, 16)) {
            throw new \InvalidArgumentException('Priority must be an unsigned integer on the range [0-65535]');
        }

        $this->priority = $priority;
    }

    /**
     * @return int
     */
    public function getWeight(): ?int
    {
        return $this->weight;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function setWeight(int $weight): void
    {
        if (!Validator::isUnsignedInteger($weight, 16)) {
            throw new \InvalidArgumentException('Weight must be an unsigned integer on the range [0-65535]');
        }

        $this->weight = $weight;
    }

    /**
     * @return string
     */
    public function getTarget(): ?string
    {
        return $this->target;
    }

    public function setTarget(string $target): void
    {
        if (false === filter_var($target, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException(sprintf('The target "%s" is not a valid URI.', $target));
        }

        $this->target = $target;
    }

    public function toText(): string
    {
        return sprintf(
            '%d %d "%s"',
            $this->priority,
            $this->weight,
            $this->target
        );
    }

    public function toWire(): string
    {
        return pack('nn', $this->priority, $this->weight).$this->target;
    }

    public function fromText(string $text): void
    {
        $rdata = explode(Tokens::SPACE, $text);
        $this->setPriority((int) array_shift($rdata));
        $this->setWeight((int) array_shift($rdata));
        $this->setTarget(trim(implode(Tokens::SPACE, $rdata), Tokens::DOUBLE_QUOTES));
    }

    public function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): void
    {
        if (false === $integers = unpack('npriority/nweight', $rdata, $offset)) {
            throw new DecodeException(static::TYPE, $rdata);
        }
        $offset += 4;
        $targetLen = ($rdLength ?? strlen($rdata)) - 4;

        $this->setTarget(substr($rdata, $offset, $targetLen));
        $this->setPriority($integers['priority']);
        $this->setWeight($integers['weight']);
        $offset += $targetLen;
    }
}
