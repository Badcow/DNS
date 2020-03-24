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
     *
     * @param string|null $name
     * @param int|null    $defaultTtl
     * @param array       $resourceRecords
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
    public function getDefaultTtl(): ?int
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
     * @param ResourceRecord[] $resourceRecords
     */
    public function fromArray(array $resourceRecords): void
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
     *
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
     *
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

    /**
     * Return the class of the zone, defaults to 'IN'.
     *
     * @return string
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
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->resourceRecords);
    }

    /**
     * @param mixed $offset
     *
     * @return ResourceRecord
     */
    public function offsetGet($offset): ResourceRecord
    {
        return $this->resourceRecords[$offset];
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
        $this->resourceRecords[$offset] = $value;
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset): void
    {
        unset($this->resourceRecords[$offset]);
    }
}
