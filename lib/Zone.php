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

namespace Badcow\DNS;

class Zone implements \Countable, \IteratorAggregate, \ArrayAccess
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
     */
    public function __construct(?string $name = null, ?int $defaultTtl = null, array $resourceRecords = [])
    {
        if (null !== $name) {
            $this->setName($name);
        }

        if (null !== $defaultTtl) {
            $this->setDefaultTtl($defaultTtl);
        }

        $this->fromArray($resourceRecords);
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function setName(string $name): void
    {
        if (!Validator::fullyQualifiedDomainName($name)) {
            throw new \InvalidArgumentException(sprintf('Zone "%s" is not a fully qualified domain name.', $name));
        }

        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getDefaultTtl(): ?int
    {
        return $this->defaultTtl;
    }

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
     * @param ResourceRecord[] $resourceRecords
     */
    public function fromArray(array $resourceRecords): void
    {
        foreach ($resourceRecords as $resourceRecord) {
            $this->addResourceRecord($resourceRecord);
        }
    }

    public function fromList(ResourceRecord ...$resourceRecords): void
    {
        $this->fromArray($resourceRecords);
    }

    public function addResourceRecord(ResourceRecord $resourceRecord): void
    {
        $this->resourceRecords[] = $resourceRecord;
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->resourceRecords);
    }

    public function count(): int
    {
        return \count($this->resourceRecords);
    }

    public function isEmpty(): bool
    {
        return empty($this->resourceRecords);
    }

    public function contains(ResourceRecord $resourceRecord): bool
    {
        foreach ($this->resourceRecords as $_item) {
            if ($_item === $resourceRecord) {
                return true;
            }
        }

        return false;
    }

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

    /**
     * Return the class of the zone, defaults to 'IN'.
     */
    public function getClass(): string
    {
        foreach ($this->resourceRecords as $resourceRecord) {
            if (null !== $resourceRecord->getClass()) {
                return $resourceRecord->getClass();
            }
        }

        return Classes::INTERNET;
    }

    /**
     * @param int|string $offset
     */
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->resourceRecords);
    }

    /**
     * @param int|string $offset
     */
    public function offsetGet($offset): ResourceRecord
    {
        return $this->resourceRecords[$offset];
    }

    /**
     * @param int|string     $offset
     * @param ResourceRecord $value
     */
    public function offsetSet($offset, $value): void
    {
        $this->resourceRecords[$offset] = $value;
    }

    /**
     * @param int|string $offset
     */
    public function offsetUnset($offset): void
    {
        unset($this->resourceRecords[$offset]);
    }
}
