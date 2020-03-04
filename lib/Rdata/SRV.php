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
 * Class SrvRdata.
 *
 * SRV is defined in RFC 2782
 *
 * @see https://tools.ietf.org/html/rfc2782
 *
 * @author Samuel Williams <sam@badcow.co>
 */
class SRV implements RdataInterface
{
    use RdataTrait;

    const TYPE = 'SRV';
    const TYPE_CODE = 33;
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
     * @var int|null
     */
    private $priority;

    /**
     * A server selection mechanism.  The weight field specifies a
     * relative weight for entries with the same priority. The range
     * is 0-65535. This is a 16 bit unsigned integer.
     *
     * @var int|null
     */
    private $weight;

    /**
     * The port on this target host of this service. The range is
     * 0-65535. This is a 16 bit unsigned integer.
     *
     * @var int|null
     */
    private $port;

    /**
     * The domain name of the target host.  There MUST be one or more
     * address records for this name, the name MUST NOT be an alias (in
     * the sense of RFC 1034 or RFC 2181).  Implementors are urged, but
     * not required, to return the address record(s) in the Additional
     * Data section.  Unless and until permitted by future standards
     * action, name compression is not to be used for this field.
     *
     * A Target of "." means that the service is decidedly not
     * available at this domain.
     *
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
     * @return string
     */
    public function getTarget(): string
    {
        return $this->target;
    }

    /**
     * @param string $target
     */
    public function setTarget(string $target): void
    {
        $this->target = $target;
    }

    /**
     * {@inheritdoc}
     */
    public function toText(): string
    {
        return sprintf('%s %s %s %s',
            $this->priority,
            $this->weight,
            $this->port,
            $this->target
        );
    }

    public function toWire(): string
    {
        return pack('nnn', $this->priority, $this->weight, $this->port).self::encodeName($this->target);
    }

    public static function fromText(string $text): RdataInterface
    {
        $rdata = explode(Tokens::SPACE, $text);
        $srv = new SRV();
        $srv->setPriority((int) $rdata[0]);
        $srv->setWeight((int) $rdata[1]);
        $srv->setPort((int) $rdata[2]);
        $srv->setTarget($rdata[3]);

        return $srv;
    }

    public static function fromWire(string $rdata, int &$offset = 0, ?int $rdLength = null): RdataInterface
    {
        $integers = unpack('npriority/nweight/nport', $rdata, $offset);
        $offset += 6;
        $srv = new self();
        $srv->setTarget(self::decodeName($rdata, $offset));
        $srv->setPriority($integers['priority']);
        $srv->setWeight($integers['weight']);
        $srv->setPort($integers['port']);

        return $srv;
    }
}
