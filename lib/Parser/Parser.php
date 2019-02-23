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

use Badcow\DNS\Classes;
use Badcow\DNS\ResourceRecord;
use Badcow\DNS\Zone;
use Badcow\DNS\Rdata;

class Parser
{
    /**
     * @var string
     */
    private $string;

    /**
     * @var string
     */
    private $previousName;

    /**
     * @var Zone
     */
    private $zone;

    /**
     * Array of methods that take an ArrayIterator and return an Rdata object. The array key is the Rdata type.
     *
     * @var array
     */
    private $rdataHandlers = [];

    /**
     * Parser constructor.
     *
     * @param array $rdataHandlers
     */
    public function __construct(array $rdataHandlers = [])
    {
        $this->rdataHandlers = array_merge(RdataHandlers::getHandlers(), $rdataHandlers);
    }

    /**
     * @param string $name
     * @param string $zone
     *
     * @return Zone
     *
     * @throws ParseException
     */
    public static function parse(string $name, string $zone): Zone
    {
        return (new self())->makeZone($name, $zone);
    }

    /**
     * @param $name
     * @param $string
     *
     * @return Zone
     *
     * @throws ParseException
     */
    public function makeZone($name, $string): Zone
    {
        $this->zone = new Zone($name);
        $this->string = Normaliser::normalise($string);

        foreach (explode(Tokens::LINE_FEED, $this->string) as $line) {
            $this->processLine($line);
        }

        return $this->zone;
    }

    /**
     * @param string $line
     *
     * @throws ParseException
     */
    private function processLine(string $line): void
    {
        $iterator = new \ArrayIterator(explode(Tokens::SPACE, $line));

        if ($this->isControlEntry($iterator)) {
            $this->processControlEntry($iterator);

            return;
        }

        $resourceRecord = new ResourceRecord();

        $this->processResourceName($iterator, $resourceRecord);
        $this->processTtl($iterator, $resourceRecord);
        $this->processClass($iterator, $resourceRecord);
        $resourceRecord->setRdata($this->extractRdata($iterator));

        $this->zone->addResourceRecord($resourceRecord);
    }

    /**
     * Processes control entries at the top of a BIND record, i.e. $ORIGIN, $TTL, $INCLUDE, etc.
     *
     * @param \ArrayIterator $iterator
     */
    private function processControlEntry(\ArrayIterator $iterator): void
    {
        if ('$TTL' === strtoupper($iterator->current())) {
            $iterator->next();
            $this->zone->setDefaultTtl((int) $iterator->current());
        }
    }

    /**
     * Processes a ResourceRecord name.
     *
     * @param \ArrayIterator $iterator
     * @param ResourceRecord $resourceRecord
     */
    private function processResourceName(\ArrayIterator $iterator, ResourceRecord $resourceRecord): void
    {
        if ($this->isResourceName($iterator)) {
            $this->previousName = $iterator->current();
            $iterator->next();
        }

        $resourceRecord->setName($this->previousName);
    }

    /**
     * Set RR's TTL if there is one.
     *
     * @param \ArrayIterator $iterator
     * @param ResourceRecord $resourceRecord
     */
    private function processTtl(\ArrayIterator $iterator, ResourceRecord $resourceRecord): void
    {
        if ($this->isTTL($iterator)) {
            $resourceRecord->setTtl($iterator->current());
            $iterator->next();
        }
    }

    /**
     * Set RR's class if there is one.
     *
     * @param \ArrayIterator $iterator
     * @param ResourceRecord $resourceRecord
     */
    private function processClass(\ArrayIterator $iterator, ResourceRecord $resourceRecord): void
    {
        if (Classes::isValid(strtoupper($iterator->current()))) {
            $resourceRecord->setClass(strtoupper($iterator->current()));
            $iterator->next();
        }
    }

    /**
     * Determine if iterant is a resource name.
     *
     * @param \ArrayIterator $iterator
     *
     * @return bool
     */
    private function isResourceName(\ArrayIterator $iterator): bool
    {
        return !(
            $this->isTTL($iterator) ||
            Classes::isValid(strtoupper($iterator->current())) ||
            RDataTypes::isValid(strtoupper($iterator->current()))
        );
    }

    /**
     * Determine if iterant is a control entry such as $TTL, $ORIGIN, $INCLUDE, etcetera.
     *
     * @param \ArrayIterator $iterator
     *
     * @return bool
     */
    private function isControlEntry(\ArrayIterator $iterator): bool
    {
        return 1 === preg_match('/^\$[A-Z0-9]+/i', $iterator->current());
    }

    /**
     * Determine if the iterant is a TTL (i.e. it is an integer).
     *
     * @param \ArrayIterator $iterator
     *
     * @return bool
     */
    private function isTTL(\ArrayIterator $iterator): bool
    {
        return 1 === preg_match('/^\d+$/', $iterator->current());
    }

    /**
     * @param \ArrayIterator $iterator
     *
     * @return RData\RDataInterface
     *
     * @throws ParseException
     */
    private function extractRdata(\ArrayIterator $iterator): Rdata\RdataInterface
    {
        $type = strtoupper($iterator->current());
        $iterator->next();

        if (array_key_exists($type, $this->rdataHandlers)) {
            return call_user_func($this->rdataHandlers[$type], $iterator);
        }

        return RdataHandlers::catchAll($type, $iterator);
    }
}
