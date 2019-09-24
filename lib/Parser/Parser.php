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
use Badcow\DNS\Rdata;
use Badcow\DNS\ResourceRecord;
use Badcow\DNS\Zone;

class Parser
{
    /**
     * @var string
     */
    private $string;

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
     * @var ResourceRecord
     */
    private $currentResourceRecord;

    /**
     * @var string
     */
    private $lastStatedDomain;

    /**
     * @var int
     */
    private $lastStatedTtl;

    /**
     * @var string
     */
    private $lastStatedClass;

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
     * @param string $name
     * @param string $string
     *
     * @return Zone
     *
     * @throws ParseException
     */
    public function makeZone(string $name, string $string): Zone
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
        $iterator = new ResourceRecordIterator($line);

        if ($this->isControlEntry($iterator)) {
            $this->processControlEntry($iterator);

            return;
        }

        $this->currentResourceRecord = new ResourceRecord();
        $this->processEntry($iterator);
        $this->zone->addResourceRecord($this->currentResourceRecord);
    }

    /**
     * @param ResourceRecordIterator $iterator
     *
     * @throws ParseException
     */
    private function processEntry(ResourceRecordIterator $iterator): void
    {
        if ($this->isTTL($iterator)) {
            $this->currentResourceRecord->setTtl((int) $iterator->current());
            $this->lastStatedTtl = (int) $iterator->current();
            $iterator->next();
            $this->processEntry($iterator);

            return;
        }

        if ($this->isClass($iterator)) {
            $this->currentResourceRecord->setClass(strtoupper($iterator->current()));
            $this->lastStatedClass = strtoupper($iterator->current());
            $iterator->next();
            $this->processEntry($iterator);

            return;
        }

        if ($this->isResourceName($iterator) && null === $this->currentResourceRecord->getName()) {
            $this->currentResourceRecord->setName($iterator->current());
            $this->lastStatedDomain = $iterator->current();
            $iterator->next();
            $this->processEntry($iterator);

            return;
        }

        if ($this->isType($iterator)) {
            $this->currentResourceRecord->setRdata($this->extractRdata($iterator));

            if (null === $this->currentResourceRecord->getName()) {
                $this->currentResourceRecord->setName($this->lastStatedDomain);
            }

            return;
        }

        throw new ParseException(sprintf('Could not parse entry "%s".', implode(' ', $iterator->getArrayCopy())));
    }

    /**
     * Processes control entries at the top of a BIND record, i.e. $ORIGIN, $TTL, $INCLUDE, etc.
     *
     * @param ResourceRecordIterator $iterator
     */
    private function processControlEntry(ResourceRecordIterator $iterator): void
    {
        if ('$TTL' === strtoupper($iterator->current())) {
            $iterator->next();
            $this->zone->setDefaultTtl((int) $iterator->current());
        }
    }

    /**
     * Determine if iterant is a resource name.
     *
     * @param ResourceRecordIterator $iterator
     *
     * @return bool
     */
    private function isResourceName(ResourceRecordIterator $iterator): bool
    {
        $iterator->next();

        if (!$iterator->valid()) {
            return false;
        }

        $isName = $this->isTTL($iterator) ||
            $this->isClass($iterator) ||
            $this->isType($iterator);
        $iterator->prev();

        return $isName;
    }

    private function isClass(ResourceRecordIterator $iterator): bool
    {
        if (!Classes::isValid($iterator->current())) {
            return false;
        }

        $iterator->next();
        $isClass = $this->isTTL($iterator) || $this->isType($iterator);
        $iterator->prev();

        return $isClass;
    }

    private function isType(ResourceRecordIterator $iterator): bool
    {
        return RDataTypes::isValid(strtoupper($iterator->current()));
    }

    /**
     * Determine if iterant is a control entry such as $TTL, $ORIGIN, $INCLUDE, etcetera.
     *
     * @param ResourceRecordIterator $iterator
     *
     * @return bool
     */
    private function isControlEntry(ResourceRecordIterator $iterator): bool
    {
        return 1 === preg_match('/^\$[A-Z0-9]+/i', $iterator->current());
    }

    /**
     * Determine if the iterant is a TTL (i.e. it is an integer).
     *
     * @param ResourceRecordIterator $iterator
     *
     * @return bool
     */
    private function isTTL(ResourceRecordIterator $iterator): bool
    {
        if (1 !== preg_match('/^\d+$/', $iterator->current())) {
            return false;
        }

        $iterator->next();
        $isTtl = $this->isClass($iterator) || $this->isType($iterator);
        $iterator->prev();

        return $isTtl;
    }

    /**
     * @param ResourceRecordIterator $iterator
     *
     * @return RData\RdataInterface
     *
     * @throws ParseException
     */
    private function extractRdata(ResourceRecordIterator $iterator): Rdata\RdataInterface
    {
        $type = strtoupper($iterator->current());
        $iterator->next();

        if (array_key_exists($type, $this->rdataHandlers)) {
            try {
                return call_user_func($this->rdataHandlers[$type], $iterator);
            } catch (\Exception $exception) {
                throw new ParseException($exception->getMessage(), null, $exception);
            }
        }

        return RdataHandlers::catchAll($type, $iterator);
    }
}
