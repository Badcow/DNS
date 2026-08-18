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
use Badcow\DNS\Rdata\RdataInterface;
use Badcow\DNS\Rdata\Types;
use Badcow\DNS\ResourceRecord;
use Badcow\DNS\Zone;
use Exception;

class Parser
{
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
     * @var string the current ORIGIN value, defaults to the Zone name
     */
    private $origin;

    /**
     * @var int|null the currently defined default TTL
     */
    private $ttl;

    /**
     * @var ZoneFileFetcherInterface|null Used to get the contents of files included through the directive
     */
    private $fetcher;

    /**
     * @var int
     */
    private $commentOptions;

    /**
     * @var bool tracks if the class has already been set on a particular line
     */
    private $classHasBeenSet = false;

    /**
     * @var bool tracks if the TTL has already been set on a particular line
     */
    private $ttlHasBeenSet = false;

    /**
     * @var bool tracks if the resource name has already been set on a particular line
     */
    private $nameHasBeenSet = false;

    /**
     * @var bool tracks if the type has already been set on a particular line
     */
    private $typeHasBeenSet = false;

    /**
     * Parser constructor.
     */
    public function __construct(array $rdataHandlers = [], ?ZoneFileFetcherInterface $fetcher = null)
    {
        $this->rdataHandlers = $rdataHandlers;
        $this->fetcher = $fetcher;
    }

    /**
     * @throws ParseException
     */
    public static function parse(string $name, string $zone, int $commentOptions = Comments::NONE): Zone
    {
        return (new self())->makeZone($name, $zone, $commentOptions);
    }

    /**
     * @throws ParseException
     */
    public function makeZone(string $name, string $string, int $commentOptions = Comments::NONE): Zone
    {
        $this->zone = new Zone($name);
        $this->origin = $name;
        $this->lastStatedDomain = $name;
        $this->commentOptions = $commentOptions;
        $this->processZone($string);

        return $this->zone;
    }

    /**
     * @throws ParseException
     */
    private function processZone(string $zone): void
    {
        $normalisedZone = Normaliser::normalise($zone, $this->commentOptions);

        foreach (explode(Tokens::LINE_FEED, $normalisedZone) as $line) {
            $this->processLine($line);
        }
    }

    /**
     * @throws ParseException
     */
    private function processLine(string $line): void
    {
        list($entry, $comment) = $this->extractComment($line);

        $this->currentResourceRecord = new ResourceRecord();
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
        $this->ttlHasBeenSet = false;
        $this->classHasBeenSet = false;
        $this->nameHasBeenSet = false;
        $this->typeHasBeenSet = false;
    }

    /**
     * @throws ParseException
     */
    private function processEntry(ResourceRecordIterator $iterator): void
    {
        if ($this->isTTL($iterator)) {
            $this->currentResourceRecord->setTtl(TimeFormat::toSeconds($iterator->current()));
            $this->ttlHasBeenSet = true;
            $iterator->next();
            $this->processEntry($iterator);

            return;
        }

        if ($this->isClass($iterator)) {
            $this->currentResourceRecord->setClass(strtoupper($iterator->current()));
            $this->classHasBeenSet = true;
            $iterator->next();
            $this->processEntry($iterator);

            return;
        }

        if ($this->isResourceName($iterator) && null === $this->currentResourceRecord->getName()) {
            $this->currentResourceRecord->setName($this->appendOrigin($iterator->current()));
            $this->nameHasBeenSet = true;
            $iterator->next();
            $this->processEntry($iterator);

            return;
        }

        if ($this->isType($iterator)) {
            $this->currentResourceRecord->setRdata($this->extractRdata($iterator));
            $this->typeHasBeenSet = true;
            $this->populateNullValues();

            return;
        }

        throw new ParseException(sprintf('Could not parse entry "%s".', (string) $iterator));
    }

    /**
     * If no domain-name, TTL, or class is set on the record, populate object with last stated value (RFC-1035).
     * If $TTL has been set, then that value will fill the resource records TTL (RFC-2308).
     *
     * @see https://www.ietf.org/rfc/rfc1035 Section 5.1
     * @see https://tools.ietf.org/html/rfc2308 Section 4
     */
    private function populateNullValues(): void
    {
        if (empty($this->currentResourceRecord->getName())) {
            $this->currentResourceRecord->setName($this->lastStatedDomain);
        } else {
            $this->lastStatedDomain = $this->currentResourceRecord->getName();
        }

        if (null === $this->currentResourceRecord->getTtl()) {
            $this->currentResourceRecord->setTtl($this->ttl ?? $this->lastStatedTtl);
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
     * Append the $ORIGIN to a subdomain if:
     *  1) the current $ORIGIN is different, and
     *  2) the subdomain is not already fully qualified, or
     *  3) the subdomain is '@'.
     *
     * @param string $subdomain the subdomain to which the $ORIGIN needs to be appended
     *
     * @return string The concatenated string of the subdomain.$ORIGIN
     */
    private function appendOrigin(string $subdomain): string
    {
        if (empty($subdomain)) {
            return $subdomain;
        }

        if ($this->origin === $this->zone->getName()) {
            return $subdomain;
        }

        if ('.' === substr($subdomain, -1, 1)) {
            return $subdomain;
        }

        if ('.' === $this->origin) {
            return $subdomain.'.';
        }

        if ('@' === $subdomain) {
            return $this->origin;
        }

        return $subdomain.'.'.$this->origin;
    }

    /**
     * Processes control entries at the top of a BIND record, i.e. $ORIGIN, $TTL, $INCLUDE, etc.
     *
     * @throws ParseException
     */
    private function processControlEntry(ResourceRecordIterator $iterator): void
    {
        if ('$TTL' === strtoupper($iterator->current())) {
            $iterator->next();
            $this->ttl = TimeFormat::toSeconds($iterator->current());
            if (null === $this->zone->getDefaultTtl()) {
                $this->zone->setDefaultTtl($this->ttl);
            }
        }

        if ('$ORIGIN' === strtoupper($iterator->current())) {
            $iterator->next();
            $this->origin = (string) $iterator->current();
        }

        if ('$INCLUDE' === strtoupper($iterator->current())) {
            $iterator->next();
            $this->includeFile($iterator);
        }
    }

    /**
     * @throws ParseException
     */
    private function includeFile(ResourceRecordIterator $iterator): void
    {
        if (null === $this->fetcher) {
            return;
        }

        list($path, $domain) = $this->extractIncludeArguments($iterator->getRemainingAsString());

        //Copy the state of the parser so as to revert back once included file has been parsed.
        $_lastStatedDomain = $this->lastStatedDomain;
        $_lastStatedClass = $this->lastStatedClass;
        $_lastStatedTtl = $this->lastStatedTtl;
        $_origin = $this->origin;
        $_ttl = $this->ttl;

        //Parse the included record.
        $this->origin = $domain ?? $_origin;
        $childRecord = $this->fetcher->fetch($path);

        if (null !== $this->currentResourceRecord->getComment()) {
            $childRecord = Tokens::SEMICOLON.$this->currentResourceRecord->getComment().Tokens::LINE_FEED.$childRecord;
        }

        $this->processZone($childRecord);

        //Revert the parser.
        $this->lastStatedDomain = $_lastStatedDomain;
        $this->lastStatedClass = $_lastStatedClass;
        $this->lastStatedTtl = $_lastStatedTtl;
        $this->origin = $_origin;
        $this->ttl = $_ttl;
    }

    /**
     * @param string $string the string proceeding the $INCLUDE directive
     *
     * @return array an array containing [$path, $domain]
     */
    private function extractIncludeArguments(string $string): array
    {
        $s = new StringIterator($string);
        $path = '';
        $domain = null;
        while ($s->valid()) {
            $path .= $s->current();
            $s->next();
            if ($s->is(Tokens::SPACE)) {
                $s->next();
                $domain = $s->getRemainingAsString();
            }
            if ($s->is(Tokens::BACKSLASH)) {
                $s->next();
            }
        }

        return [$path, $domain];
    }

    /**
     * Determine if iterant is a resource name.
     */
    private function isResourceName(ResourceRecordIterator $iterator): bool
    {
        if ($this->nameHasBeenSet) {
            return false;
        }

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
     * @param string|null $origin the previously assumed resource record parameter, either 'TTL' or NULL
     */
    private function isClass(ResourceRecordIterator $iterator, $origin = null): bool
    {
        if ($this->classHasBeenSet) {
            return false;
        }

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
     */
    private function isType(ResourceRecordIterator $iterator): bool
    {
        if ($this->typeHasBeenSet) {
            return false;
        }

        return Types::isValid(strtoupper($iterator->current())) || array_key_exists($iterator->current(), $this->rdataHandlers);
    }

    /**
     * Determine if iterant is a control entry such as $TTL, $ORIGIN, $INCLUDE, etcetera.
     */
    private function isControlEntry(ResourceRecordIterator $iterator): bool
    {
        return 1 === preg_match('/^\$[A-Z0-9]+/i', $iterator->current());
    }

    /**
     * Determine if the iterant is a TTL (i.e. it is an integer after domain-name).
     *
     * @param string $origin the previously assumed resource record parameter, either 'CLASS' or NULL
     */
    private function isTTL(ResourceRecordIterator $iterator, $origin = null): bool
    {
        if ($this->ttlHasBeenSet) {
            return false;
        }

        if (!TimeFormat::isTimeFormat($iterator->current())) {
            return false;
        }

        if ($iterator->key() < 1) {
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
     * @throws ParseException
     */
    private function extractRdata(ResourceRecordIterator $iterator): RdataInterface
    {
        $type = strtoupper($iterator->current());
        $iterator->next();

        if (array_key_exists($type, $this->rdataHandlers)) {
            return $this->callRdataHandler($type, $iterator);
        }

        try {
            return Factory::textToRdataType($type, $iterator->getRemainingAsString());
        } catch (Exception $exception) {
            throw new ParseException(sprintf('Could not extract Rdata from resource record "%s".', (string) $iterator), null, $exception);
        }
    }

    private function callRdataHandler(string $type, ResourceRecordIterator $iterator): RdataInterface
    {
        $rdataInterface = call_user_func($this->rdataHandlers[$type], $iterator);
        if (!$rdataInterface instanceof RdataInterface) {
            throw new \UnexpectedValueException(sprintf('Rdata handler must return instance of Badcow\DNS\Rdata\RdataInterface; "%s" returned instead.', gettype($rdataInterface)));
        }

        return $rdataInterface;
    }
}
