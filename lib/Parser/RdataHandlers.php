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
    /**
     * Mappings of RData type to its handler method.
     *
     * @var array
     */
    private static $handlers = [
        Rdata\APL::TYPE => __CLASS__.'::handleAplRdata',
        Rdata\CAA::TYPE => __CLASS__.'::handleCaaRdata',
        Rdata\LOC::TYPE => __CLASS__.'::handleLocRdata',
        Rdata\SSHFP::TYPE => __CLASS__.'::handleSshfpRdata',
        Rdata\TXT::TYPE => __CLASS__.'::handleTxtRdata',
        Rdata\URI::TYPE => __CLASS__.'::handleUriRdata',
    ];

    /**
     * @return array
     */
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

    /**
     * @param \ArrayIterator $iterator
     *
     * @return Rdata\CAA
     */
    public static function handleCaaRdata(\ArrayIterator $iterator): Rdata\CAA
    {
        $flag = (int) self::pop($iterator);
        $tag = (string) self::pop($iterator);
        $value = new StringIterator();

        $string = new StringIterator(implode(Tokens::SPACE, self::getAllRemaining($iterator)));
        self::handleTxt($string, $value);

        return Rdata\Factory::caa($flag, $tag, $value);
    }

    /**
     * @param \ArrayIterator $iterator
     *
     * @return Rdata\SSHFP
     */
    public static function handleSshfpRdata(\ArrayIterator $iterator): Rdata\SSHFP
    {
        return Rdata\Factory::SSHFP((int) self::pop($iterator), (int) self::pop($iterator), self::pop($iterator));
    }

    /**
     * @param \ArrayIterator $iterator
     *
     * @return Rdata\URI
     */
    public static function handleUriRdata(\ArrayIterator $iterator): Rdata\URI
    {
        $priority = (int) self::pop($iterator);
        $weight = (int) self::pop($iterator);
        $target = trim(implode(' ', self::getAllRemaining($iterator)), '"');
        $target = str_replace(' ', '%20', $target);

        return Rdata\Factory::URI($priority, $weight, $target);
    }

    /**
     * Returns RData instances for types that do not have explicitly declared handler methods.
     *
     * @param string         $type
     * @param \ArrayIterator $iterator
     *
     * @return Rdata\RdataInterface
     */
    public static function catchAll(string $type, \ArrayIterator $iterator): Rdata\RdataInterface
    {
        $rdataFactoryMethod = [Rdata\Factory::class, $type];

        if (!is_callable($rdataFactoryMethod)) {
            return new Rdata\PolymorphicRdata($type, implode(Tokens::SPACE, self::getAllRemaining($iterator)));
        }

        return call_user_func_array($rdataFactoryMethod, self::getAllRemaining($iterator));
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
