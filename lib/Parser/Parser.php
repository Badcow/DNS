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

namespace Badcow\DNS\Parser;

use Badcow\DNS\Classes;
use Badcow\DNS\Rdata\Factory;
use Badcow\DNS\Rdata\PTR;
use Badcow\DNS\Rdata\RdataInterface;
use Badcow\DNS\Rdata\Types;
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
     * @var callable[]
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
        $this->rdataHandlers = array_merge(
            [PTR::TYPE => [$this, 'ptrHandler']],
            $rdataHandlers
        );
    }

    /**
     * @param string $name
     * @param string $zone
     * @param int    $commentOptions
     *
     * @return Zone
     *
     * @throws ParseException
     */
    public static function parse(string $name, string $zone, int $commentOptions = Comments::NONE): Zone
    {
        return (new self())->makeZone($name, $zone, $commentOptions);
    }

    /**
     * @param string $name
     * @param string $string
     * @param int    $commentOptions
     *
     * @return Zone
     *
     * @throws ParseException
     */
    public function makeZone(string $name, string $string, int $commentOptions = Comments::NONE): Zone
    {
        $this->zone = new Zone($name);
        $this->string = Normaliser::normalise($string, $commentOptions);

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
        list($entry, $comment) = $this->extractComment($line);

        $this->currentResourceRecord = new ResourceRecord();
        $this->currentResourceRecord->setTtl($this->zone->getDefaultTtl());
        $this->currentResourceRecord->setComment($comment);

        if ('' === $entry) {
            $this->zone->addResourceRecord($this->currentResourceRecord);

            return;
        }

        $iterator = new ResourceRecordIterator($entry);

        if ($this->isControlEntry($iterator)) {
            $this->processControlEntry($iterator);

            return;
        }

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
            $this->currentResourceRecord->setTtl(TimeFormat::toSeconds($iterator->current()));
            $iterator->next();
            $this->processEntry($iterator);

            return;
        }

        if ($this->isClass($iterator)) {
            $this->currentResourceRecord->setClass(strtoupper($iterator->current()));
            $iterator->next();
            $this->processEntry($iterator);

            return;
        }

        if ($this->isResourceName($iterator) && null === $this->currentResourceRecord->getName()) {
            $this->currentResourceRecord->setName($iterator->current());
            $iterator->next();
            $this->processEntry($iterator);

            return;
        }

        if ($this->isType($iterator)) {
            $this->currentResourceRecord->setRdata($this->extractRdata($iterator));
            $this->populateWithLastStated();

            return;
        }

        throw new ParseException(sprintf('Could not parse entry "%s".', implode(' ', $iterator->getArrayCopy())));
    }

    /**
     * If no domain-name, TTL, or class is set on the record, populate object with last stated value.
     *
     * @see https://www.ietf.org/rfc/rfc1035 Section 5.1
     */
    private function populateWithLastStated(): void
    {
        if (null === $this->currentResourceRecord->getName()) {
            $this->currentResourceRecord->setName($this->lastStatedDomain);
        } else {
            $this->lastStatedDomain = $this->currentResourceRecord->getName();
        }

        if (null === $this->currentResourceRecord->getTtl()) {
            $this->currentResourceRecord->setTtl($this->lastStatedTtl);
        } else {
            $this->lastStatedTtl = $this->currentResourceRecord->getTtl();
        }

        if (null === $this->currentResourceRecord->getClass()) {
            $this->currentResourceRecord->setClass($this->lastStatedClass);
        } else {
            $this->lastStatedClass = $this->currentResourceRecord->getClass();
        }
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
            $this->zone->setDefaultTtl(TimeFormat::toSeconds($iterator->current()));
        }

        if ('$ORIGIN' === strtoupper($iterator->current())) {
            $iterator->next();
            $this->zone->setName((string) $iterator->current());
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
        // Look ahead and determine if the next token is a TTL, Class, or valid Type.
        $iterator->next();

        if (!$iterator->valid()) {
            return false;
        }

        $isName = $this->isTTL($iterator) ||
            $this->isClass($iterator, 'DOMAIN') ||
            $this->isType($iterator);
        $iterator->prev();

        if (!$isName) {
            return false;
        }

        if (0 === $iterator->key()) {
            return true;
        }

        return false;
    }

    /**
     * Determine if iterant is a class.
     *
     * @param ResourceRecordIterator $iterator
     * @param string|null            $origin   the previously assumed resource record parameter, either 'TTL' or NULL
     *
     * @return bool
     */
    private function isClass(ResourceRecordIterator $iterator, $origin = null): bool
    {
        if (!Classes::isValid($iterator->current())) {
            return false;
        }

        $iterator->next();
        if ('TTL' === $origin) {
            $isClass = $this->isType($iterator);
        } else {
            $isClass = $this->isTTL($iterator, 'CLASS') || $this->isType($iterator);
        }
        $iterator->prev();

        return $isClass;
    }

    /**
     * Determine if current iterant is an Rdata type string.
     *
     * @param ResourceRecordIterator $iterator
     *
     * @return bool
     */
    private function isType(ResourceRecordIterator $iterator): bool
    {
        return Types::isValid(strtoupper($iterator->current())) || array_key_exists($iterator->current(), $this->rdataHandlers);
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
     * @param string                 $origin   the previously assumed resource record parameter, either 'CLASS' or NULL
     *
     * @return bool
     */
    private function isTTL(ResourceRecordIterator $iterator, $origin = null): bool
    {
        if (!TimeFormat::isTimeFormat($iterator->current())) {
            return false;
        }

        $iterator->next();
        if ('CLASS' === $origin) {
            $isTtl = $this->isType($iterator);
        } else {
            $isTtl = $this->isClass($iterator, 'TTL') || $this->isType($iterator);
        }
        $iterator->prev();

        return $isTtl;
    }

    /**
     * Split a DNS zone line into a resource record and a comment.
     *
     * @param string $rr
     *
     * @return array [$entry, $comment]
     */
    private function extractComment(string $rr): array
    {
        $string = new StringIterator($rr);
        $entry = '';
        $comment = null;

        while ($string->valid()) {
            //If a semicolon is within double quotes, it will not be treated as the beginning of a comment.
            $entry .= $this->extractDoubleQuotedText($string);

            if ($string->is(Tokens::SEMICOLON)) {
                $string->next();
                $comment = $string->getRemainingAsString();

                break;
            }

            $entry .= $string->current();
            $string->next();
        }

        return [$entry, $comment];
    }

    /**
     * Extract text within double quotation context.
     *
     * @param StringIterator $string
     *
     * @return string
     */
    private function extractDoubleQuotedText(StringIterator $string): string
    {
        if ($string->isNot(Tokens::DOUBLE_QUOTES)) {
            return '';
        }

        $entry = $string->current();
        $string->next();

        while ($string->isNot(Tokens::DOUBLE_QUOTES)) {
            //If the current char is a backslash, treat the next char as being escaped.
            if ($string->is(Tokens::BACKSLASH)) {
                $entry .= $string->current();
                $string->next();
            }
            $entry .= $string->current();
            $string->next();
        }

        return $entry;
    }

    /**
     * @param ResourceRecordIterator $iterator
     *
     * @return RdataInterface
     *
     * @throws ParseException
     */
    private function extractRdata(ResourceRecordIterator $iterator): RdataInterface
    {
        $type = strtoupper($iterator->current());
        $iterator->next();

        if (array_key_exists($type, $this->rdataHandlers)) {
            return call_user_func($this->rdataHandlers[$type], $iterator);
        }

        try {
            return Factory::textToRdataType($type, $iterator->getRemainingAsString());
        } catch (\Exception $exception) {
            throw new ParseException(sprintf('Could not extract Rdata from resource record "%s".', (string) $iterator), null, $exception);
        }
    }

    /**
     * This handler addresses the special case where an integer resource name could be confused for a TTL, for instance:
     * 50 IN PTR mx1.acme.com.
     *
     * In the above, if the integer is below 256 then it is assumed to represent an octet of an IPv4 address.
     *
     * @param ResourceRecordIterator $iterator
     *
     * @return PTR
     */
    private function ptrHandler(ResourceRecordIterator $iterator): PTR
    {
        if (null === $this->currentResourceRecord->getName() && null !== $this->currentResourceRecord->getTtl()) {
            if ($this->currentResourceRecord->getTtl() < 256) {
                $this->currentResourceRecord->setName((string) $this->currentResourceRecord->getTtl());
                $this->currentResourceRecord->setTtl(null);
            }
        }

        /** @var PTR $ptr */
        $ptr = PTR::fromText($iterator->getRemainingAsString());

        return $ptr;
    }
}
