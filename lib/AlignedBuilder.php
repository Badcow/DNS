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

use Badcow\DNS\Parser\Tokens;
use Badcow\DNS\Rdata\A;
use Badcow\DNS\Rdata\AAAA;
use Badcow\DNS\Rdata\CNAME;
use Badcow\DNS\Rdata\DNAME;
use Badcow\DNS\Rdata\HINFO;
use Badcow\DNS\Rdata\LOC;
use Badcow\DNS\Rdata\MX;
use Badcow\DNS\Rdata\NS;
use Badcow\DNS\Rdata\NSEC3;
use Badcow\DNS\Rdata\NSEC3PARAM;
use Badcow\DNS\Rdata\PTR;
use Badcow\DNS\Rdata\RdataInterface;
use Badcow\DNS\Rdata\RRSIG;
use Badcow\DNS\Rdata\SOA;
use Badcow\DNS\Rdata\SRV;
use Badcow\DNS\Rdata\TXT;

class AlignedBuilder
{
    /**
     * The order in which Resource Records should appear in a zone.
     *
     * @var array
     */
    private $order = [
        SOA::TYPE,
        NS::TYPE,
        A::TYPE,
        AAAA::TYPE,
        CNAME::TYPE,
        DNAME::TYPE,
        MX::TYPE,
        LOC::TYPE,
        HINFO::TYPE,
        TXT::TYPE,
        PTR::TYPE,
        SRV::TYPE,
        NSEC3::TYPE,
        NSEC3PARAM::TYPE,
        RRSIG::TYPE,
    ];

    /**
     * @var callable[] array of Rdata type indexed, callables that handle the output formatting of Rdata
     */
    private $rdataFormatters = [];

    public function __construct()
    {
        $this->rdataFormatters = AlignedRdataFormatters::$rdataFormatters;
    }

    /**
     * Adds or changes an Rdata output formatter.
     *
     * @param string   $type      the Rdata type to be handled by the $formatter
     * @param callable $formatter callable that will handle the output formatting of the Rdata
     */
    public function addRdataFormatter(string $type, callable $formatter): void
    {
        $this->rdataFormatters[$type] = $formatter;
    }

    public function getRdataFormatters(): array
    {
        return $this->rdataFormatters;
    }

    /**
     * @return string[]
     */
    public function getOrder(): array
    {
        return $this->order;
    }

    /**
     * Set the order in which Resource Records should appear in a zone..
     *
     * @param string[] $order Simple string array of Rdata types
     */
    public function setOrder(array $order): void
    {
        $this->order = $order;
    }

    /**
     * Build an aligned BIND zone file.
     */
    public function build(Zone $zone): string
    {
        $master = self::generateControlEntries($zone);
        $resourceRecords = $zone->getResourceRecords();
        $current = SOA::TYPE;
        usort($resourceRecords, [__CLASS__, 'compareResourceRecords']);

        list($namePadding, $ttlPadding, $typePadding, $classPadding, $rdataPadding) = self::getPadding($zone);

        foreach ($resourceRecords as $resourceRecord) {
            $rdata = $resourceRecord->getRdata();
            if (null == $rdata) {
                continue;
            }

            if ($rdata->getType() !== $current) {
                $master .= Tokens::LINE_FEED.Tokens::SEMICOLON.Tokens::SPACE.$rdata->getType().' RECORDS'.Tokens::LINE_FEED;
                $current = $rdata->getType();
            }

            $master .= sprintf(
                '%s %s %s %s %s',
                str_pad((string) $resourceRecord->getName(), $namePadding, Tokens::SPACE, STR_PAD_RIGHT),
                str_pad((string) $resourceRecord->getTtl(), $ttlPadding, Tokens::SPACE, STR_PAD_RIGHT),
                str_pad((string) $resourceRecord->getClass(), $classPadding, Tokens::SPACE, STR_PAD_RIGHT),
                str_pad($rdata->getType(), $typePadding, Tokens::SPACE, STR_PAD_RIGHT),
                $this->generateRdataOutput($rdata, $rdataPadding)
            );

            $master .= self::generateComment($resourceRecord);
            $master .= Tokens::LINE_FEED;
        }

        return $master;
    }

    /**
     * Returns the control entries as strings.
     */
    private static function generateControlEntries(Zone $zone): string
    {
        $master = '$ORIGIN '.$zone->getName().Tokens::LINE_FEED;
        if (null !== $zone->getDefaultTtl()) {
            $master .= '$TTL '.$zone->getDefaultTtl().Tokens::LINE_FEED;
        }

        return $master;
    }

    /**
     * Returns a comment string if the comments are not null, returns empty string otherwise.
     */
    private static function generateComment(ResourceRecord $resourceRecord): string
    {
        if (null !== $resourceRecord->getComment()) {
            return Tokens::SEMICOLON.Tokens::SPACE.$resourceRecord->getComment();
        }

        return '';
    }

    /**
     * Compares two ResourceRecords to determine which is the higher order. Used with the usort() function.
     *
     * @param ResourceRecord $a The first ResourceRecord
     * @param ResourceRecord $b The second ResourceRecord
     *
     * @return int $a is higher precedence than $b if return value is less than 0.
     *             $b is higher precedence than $a if return value is greater than 0.
     *             $a and $b have the same precedence if the return value is 0.
     */
    public function compareResourceRecords(ResourceRecord $a, ResourceRecord $b): int
    {
        $a_rdata = (null === $a->getRdata()) ? '' : $a->getRdata()->toText();
        $b_rdata = (null === $b->getRdata()) ? '' : $b->getRdata()->toText();

        //If the types are the same, do a simple alphabetical comparison.
        if ($a->getType() === $b->getType()) {
            return strcmp($a->getName().$a_rdata, $b->getName().$b_rdata);
        }

        //Find the precedence (if any) for the two types.
        $_a = array_search($a->getType(), $this->order);
        $_b = array_search($b->getType(), $this->order);

        //If neither types have defined precedence.
        if (!is_int($_a) && !is_int($_b)) {
            return strcmp($a->getType() ?? '', $b->getType() ?? '');
        }

        //If both types have defined precedence.
        if (is_int($_a) && is_int($_b)) {
            return $_a - $_b;
        }

        //If only $b has defined precedence.
        if (false === $_a) {
            return 1;
        }

        //If only $a has defined precedence.
        return -1;
    }

    /**
     * Composes the RDATA of the Resource Record.
     *
     * @param RdataInterface $rdata   the Rdata to be formatted
     * @param int            $padding the number of spaces before the Rdata column
     */
    private function generateRdataOutput(RdataInterface $rdata, int $padding): string
    {
        if (!array_key_exists($rdata->getType(), $this->rdataFormatters)) {
            return $rdata->toText();
        }

        $formatted = call_user_func($this->rdataFormatters[$rdata->getType()], $rdata, $padding);
        if (!is_string($formatted)) {
            throw new \UnexpectedValueException(sprintf('Formatter for RType "%s" returned object type "%s", string expected.', $rdata->getType(), gettype($formatted)));
        }

        return $formatted;
    }

    /**
     * Get the padding required for a zone.
     *
     * @param Zone $zone the DNS Zone being processed
     *
     * @return int[] Array order: [name, ttl, type, class, rdata]
     */
    private static function getPadding(Zone $zone): array
    {
        $name = $ttl = $type = 0;
        $class = 2;

        /** @var ResourceRecord $resourceRecord */
        foreach ($zone as $resourceRecord) {
            $name = max($name, strlen($resourceRecord->getName() ?? ''));
            $ttl = max($ttl, strlen((string) $resourceRecord->getTtl()));
            $class = max($class, strlen($resourceRecord->getClass() ?? ''));
            $type = max($type, strlen($resourceRecord->getType() ?? ''));
        }

        return [
            $name,
            $ttl,
            $type,
            $class,
            $name + $ttl + $class + $type + 4,
        ];
    }
}
