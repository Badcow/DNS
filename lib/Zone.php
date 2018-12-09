<?php

/*
 * This file is part of Badcow DNS Library.
 *
 * (c) Samuel Williams <sam@badcow.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Badcow\DNS;

class Zone implements \Countable, \IteratorAggregate
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var int
     */
    protected $defaultTtl;

    /**
     * @var ResourceRecord[]
     */
    private $resourceRecords = [];

    /**
     * Zone constructor.
     *
     * @param string $name
     * @param int    $defaultTtl
     * @param array  $resourceRecords
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(string $name = null, int $defaultTtl = null, array $resourceRecords = [])
    {
        $this->name = $name;
        $this->defaultTtl = $defaultTtl;
        $this->fromArray($resourceRecords);
    }

    /**
     * @param string $name
     *
     * @throws \InvalidArgumentException
     */
    public function setName(string $name): void
    {
        if (!Validator::fullyQualifiedDomainName($name)) {
            throw new \InvalidArgumentException(sprintf('Zone "%s" is not a fully qualified domain name.', $name));
        }

        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getDefaultTtl(): int
    {
        return $this->defaultTtl;
    }

    /**
     * @param int $defaultTtl
     */
    public function setDefaultTtl(int $defaultTtl): void
    {
        $this->defaultTtl = $defaultTtl;
    }

    /**
     * @return ResourceRecord[]
     */
    public function getResourceRecords(): array
    {
        return $this->resourceRecords;
    }

    /**
     * @param array $resourceRecords
     */
    public function fromArray(array $resourceRecords)
    {
        foreach ($resourceRecords as $resourceRecord) {
            $this->addResourceRecord($resourceRecord);
        }
    }

    /**
     * @param ResourceRecord ...$resourceRecords
     */
    public function fromList(ResourceRecord ...$resourceRecords): void
    {
        $this->fromArray($resourceRecords);
    }

    /**
     * @param ResourceRecord $resourceRecord
     */
    public function addResourceRecord(ResourceRecord $resourceRecord): void
    {
        $this->resourceRecords[] = $resourceRecord;
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->resourceRecords);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return \count($this->resourceRecords);
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->resourceRecords);
    }

    /**
     * @param ResourceRecord $resourceRecord
     * @return bool
     */
    public function contains(ResourceRecord $resourceRecord): bool
    {
        foreach ($this->resourceRecords as $_item) {
            if ($_item === $resourceRecord) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param ResourceRecord $resourceRecord
     * @return bool
     */
    public function remove(ResourceRecord $resourceRecord): bool
    {
        foreach ($this->resourceRecords as $key => $_item) {
            if ($_item === $resourceRecord) {
                unset($this->resourceRecords[$key]);

                return true;
            }
        }

        return false;
    }
}
