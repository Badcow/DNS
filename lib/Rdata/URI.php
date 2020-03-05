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

/**
 * {@link https://tools.ietf.org/html/rfc7553}.
 */
class URI implements RdataInterface
{
    use RdataTrait;

    const TYPE = 'URI';
    const TYPE_CODE = 256;
    const MAX_PRIORITY = 65535;
    const MAX_WEIGHT = 65535;

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
     * @param int $priority
     *
     * @throws \InvalidArgumentException
     */
    public function setPriority(int $priority): void
    {
        if ($priority < 0 || $priority > static::MAX_PRIORITY) {
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
     * @param int $weight
     *
     * @throws \InvalidArgumentException
     */
    public function setWeight(int $weight): void
    {
        if ($weight < 0 || $weight > static::MAX_WEIGHT) {
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

    /**
     * @param string $target
     */
    public function setTarget(string $target): void
    {
        if (false === filter_var($target, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException(sprintf('The target "%s" is not a valid URI.', $target));
        }

        $this->target = $target;
    }

    /**
     * {@inheritdoc}
     */
    public function toText(): string
    {
        return sprintf('%d %d "%s"',
            $this->priority,
            $this->weight,
            $this->target
        );
    }

    public function toWire(): string
    {
        return pack('nn', $this->priority, $this->weight).$this->target;
    }

    public static function fromText(string $text): RdataInterface
    {
        $rdata = explode(Tokens::SPACE, $text);
        $uri = new self();
        $uri->setPriority((int) array_shift($rdata));
        $uri->setWeight((int) array_shift($rdata));
        $target = implode(' ', $rdata);
        $uri->setTarget(trim($target, '"'));

        return $uri;
    }

    public static function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): RdataInterface
    {
        $integers = unpack('npriority/nweight', $rdata, $offset);
        $offset += 4;
        $targetLen = ($rdLength ?? strlen($rdata)) - 4;

        $uri = new self();
        $uri->setTarget(substr($rdata, $offset, $targetLen));
        $uri->setPriority($integers['priority']);
        $uri->setWeight($integers['weight']);
        $offset += $targetLen;

        return $uri;
    }
}
