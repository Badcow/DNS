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
 * Class SrvRdata.
 *
 * SRV is defined in RFC 2782
 *
 * @see https://tools.ietf.org/html/rfc2782
 *
 * @author Samuel Williams <sam@badcow.co>
 */
class SRV extends CNAME
{
    const TYPE = 'SRV';

    const HIGHEST_PORT = 65535;

    const MAX_PRIORITY = 65535;

    const MAX_WEIGHT = 65535;

    /**
     * The priority of this target host. A client MUST attempt to
     * contact the target host with the lowest-numbered priority it can
     * reach; target hosts with the same priority SHOULD be tried in an
     * order defined by the weight field. The range is 0-65535. This
     * is a 16 bit unsigned integer.
     *
     * @var int
     */
    private $priority;

    /**
     * A server selection mechanism.  The weight field specifies a
     * relative weight for entries with the same priority. The range
     * is 0-65535. This is a 16 bit unsigned integer.
     *
     * @var int
     */
    private $weight;

    /**
     * The port on this target host of this service. The range is
     * 0-65535. This is a 16 bit unsigned integer.
     *
     * @var int
     */
    private $port;

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
     * @return int
     */
    public function getPort(): ?int
    {
        return $this->port;
    }

    /**
     * @param int $port
     *
     * @throws \InvalidArgumentException
     */
    public function setPort(int $port): void
    {
        if ($port < 0 || $port > static::HIGHEST_PORT) {
            throw new \InvalidArgumentException('Port must be an unsigned integer on the range [0-65535]');
        }

        $this->port = $port;
    }

    /**
     * {@inheritdoc}
     */
    public function output(): string
    {
        return sprintf('%s %s %s %s',
            $this->priority,
            $this->weight,
            $this->port,
            $this->target
        );
    }
}
