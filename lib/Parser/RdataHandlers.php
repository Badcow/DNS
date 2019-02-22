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

use Badcow\DNS\Rdata;

class RdataHandlers
{
    private static $handlers = [
        Rdata\LOC::TYPE => __CLASS__.'::handleLocRdata',
        Rdata\TXT::TYPE => __CLASS__.'::handleTxtRdata',
        Rdata\APL::TYPE => __CLASS__.'::handleAplRdata',
    ];

    public static function getHandlers(): array
    {
        return self::$handlers;
    }

    /**
     * Transform a DMS string to a decimal representation. Used for LOC records.
     *
     * @param int    $deg        Degrees
     * @param int    $min        Minutes
     * @param float  $sec        Seconds
     * @param string $hemisphere Either 'N', 'S', 'E', or 'W'
     *
     * @return float
     */
    public static function dmsToDecimal(int $deg, int $min, float $sec, string $hemisphere): float
    {
        $multiplier = ('S' === $hemisphere || 'W' === $hemisphere) ? -1 : 1;

        return $multiplier * ($deg + ($min / 60) + ($sec / 3600));
    }

    /**
     * @param \ArrayIterator $iterator
     *
     * @return Rdata\LOC
     */
    public static function handleLocRdata(\ArrayIterator $iterator): Rdata\LOC
    {
        $lat = self::dmsToDecimal((int) self::pop($iterator), (int) self::pop($iterator), (float) self::pop($iterator), self::pop($iterator));
        $lon = self::dmsToDecimal((int) self::pop($iterator), (int) self::pop($iterator), (float) self::pop($iterator), self::pop($iterator));

        return Rdata\Factory::Loc(
            $lat,
            $lon,
            (float) self::pop($iterator),
            (float) self::pop($iterator),
            (float) self::pop($iterator),
            (float) self::pop($iterator)
        );
    }

    /**
     * @param \ArrayIterator $iterator
     *
     * @return Rdata\APL
     *
     * @throws ParseException
     */
    public static function handleAplRdata(\ArrayIterator $iterator): Rdata\APL
    {
        $rdata = new Rdata\APL();

        while ($iterator->valid()) {
            $matches = [];
            if (1 !== preg_match('/^(?<negate>!)?[1-2]:(?<block>.+)$/i', $iterator->current(), $matches)) {
                throw new ParseException(sprintf('"%s" is not a valid IP range.', $iterator->current()));
            }

            $ipBlock = \IPBlock::create($matches['block']);
            $rdata->addAddressRange($ipBlock, '!' !== $matches['negate']);
            $iterator->next();
        }

        return $rdata;
    }

    /**
     * @param \ArrayIterator $iterator
     *
     * @return Rdata\TXT
     */
    public static function handleTxtRdata(\ArrayIterator $iterator): Rdata\TXT
    {
        $string = new StringIterator(implode(Tokens::SPACE, self::getAllRemaining($iterator)));
        $txt = new StringIterator();

        while ($string->valid()) {
            self::handleTxt($string, $txt);
            $string->next();
        }

        return Rdata\Factory::txt((string) $txt);
    }

    public static function catchAll(string $type, \ArrayIterator $iterator): Rdata\RdataInterface
    {
        if (!Rdata\Factory::isTypeImplemented($type)) {
            return new PolymorphicRdata($type, implode(Tokens::SPACE, self::getAllRemaining($iterator)));
        }

        return call_user_func_array([Rdata\Factory::class, $type], self::getAllRemaining($iterator));
    }

    /**
     * @param StringIterator $string
     * @param StringIterator $txt
     */
    private static function handleTxt(StringIterator $string, StringIterator $txt): void
    {
        if ($string->isNot(Tokens::DOUBLE_QUOTES)) {
            return;
        }

        $string->next();

        while ($string->isNot(Tokens::DOUBLE_QUOTES) && $string->valid()) {
            if ($string->is(Tokens::BACKSLASH)) {
                $string->next();
            }

            $txt->append($string->current());
            $string->next();
        }
    }

    /**
     * Return current entry and moves the iterator to the next entry.
     *
     * @param \ArrayIterator $iterator
     *
     * @return string
     */
    private static function pop(\ArrayIterator $iterator): string
    {
        $current = $iterator->current();
        $iterator->next();

        return $current;
    }

    /**
     * Get all the remaining values of an iterator as an array.
     *
     * @param \ArrayIterator $iterator
     *
     * @return array
     */
    private static function getAllRemaining(\ArrayIterator $iterator): array
    {
        $values = [];
        while ($iterator->valid()) {
            $values[] = $iterator->current();
            $iterator->next();
        }

        return $values;
    }
}
