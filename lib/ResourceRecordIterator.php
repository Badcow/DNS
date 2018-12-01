<?php

declare(strict_types=1);

namespace Badcow\DNS;

final class ResourceRecordIterator implements \Iterator
{
    /**
     * @var ResourceRecord[]
     */
    private $resourceRecords;

    /**
     * @var int
     */
    private $position;

    public function __construct(Zone $zone)
    {
        $this->resourceRecords = $zone->getResourceRecords();
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function valid(): bool
    {
        return $this->position < \count($this->resourceRecords);
    }

    public function key(): int
    {
        return $this->position;
    }

    public function current(): ResourceRecord
    {
        return $this->resourceRecords[$this->position];
    }

    public function next(): void
    {
        ++$this->position;
    }
}
