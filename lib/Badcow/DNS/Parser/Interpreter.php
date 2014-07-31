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
 
class Interpreter
{
    /**
     * @constant
     */
    const RNAME_PATTERN = '/^((?:(?=^.{1,254}$)(^(?:(?!\d+\.)[a-zA-Z0-9_\-]{1,63}\.?)+(?:[a-zA-Z]{2,})))|(?:@))\s/';

    /**
     * @constant
     */
    const CLASS_PATTERN = '/\s(IN|HS|CH)\s/';

    /**
     * Return an associative array of the line [0]
     * without comments and comments [1]
     *
     * @param string $line
     * @return array
     * @throws ParseException
     */
    public static function stripCommentFromLine($line)
    {
        $result = preg_match('/;(.*)$/', $line, $matches);
        $comment = '';

        if ($result === false) {
            throw new ParseException('Could not strip comments.');
        }

        if ($result == 1) {
            $comment = trim($matches[1]);
        }

        $line = trim(preg_replace(array('/(?:\s+|\t+)/', '/;.*$/'), array(' ', ''), $line));

        return array($line, $comment);
    }

    /**
     * Get the resource name
     *
     * @param string $line
     * @return string
     * @throws ParseException
     */
    public static function getResourceNameFromLine($line)
    {
        $line = trim($line);
        $result = preg_match(self::RNAME_PATTERN, $line, $matches);

        if (false === $result) {
            throw new ParseException(sprintf('Could not get resource name from line "%s".', $line));
        }

        if (0 === $result) {
            return '';
        }

        return $matches[1];
    }

    /**
     * Get the class from a line (if there is one)
     *
     * @param $line
     * @return string|null
     * @throws ParseException
     */
    public static function getClassFromLine($line)
    {
        $line = trim($line);
        $result = preg_match(self::CLASS_PATTERN, $line, $matches);

        if ($result === false) {
            throw new ParseException(sprintf('Could not get class from line "%s".', $line));
        }

        if ($result === 0) {
            return null;
        }

        $class = $matches[1];
        if (!array_key_exists($class, Classes::$classes)) {
            throw new ParseException(sprintf('Class "%s" in line "%s" is not a valid class.', $class, $line));
        }

        return $class;
    }

    /**
     * Expands a Zone to logical lines with comments
     *
     * @param string $zoneData
     * @return array
     */
    public static function expand($zoneData)
    {
        $iterator = new \ArrayIterator(explode("\n", $zoneData));
        $lines = array();

        while ($iterator->valid()) {
            if ($iterator->current() == '' || preg_match('/^\s+$/', $iterator->current())) {
                $iterator->next();
                continue;
            }

            $values = self::parseLine($iterator);
            $lines[] = array(
                'line' => $values[0],
                'comment' => $values[1],
            );

            $iterator->next();
        }

        return $lines;
    }

    /**
     * Parses a line(s) and returns the logic
     * part and comment part
     *
     * @param \ArrayIterator $iterator
     * @return array
     */
    public static function parseLine(\ArrayIterator $iterator)
    {
        list($line, $comment) = Interpreter::stripCommentFromLine($iterator->current());

        if (strpos($line, '(') !== false) {
            $comment = (array) $comment;
            $flag = true;
            while ($flag) {
                $iterator->next();
                list($_line, $comment[]) = Interpreter::stripCommentFromLine($iterator->current());
                $line .= ' ' . $_line;

                if (strpos($_line, ')') !== false) {
                    $line = trim(preg_replace('/\s*[\(\)]\s*/', ' ', $line)); //Remove the brackets
                    $flag = false;
                }
            }

            $comment = trim(preg_replace('/\s+/', ' ', implode(' ', $comment)));
        }

        return array(
            $line,
            $comment,
        );
    }
}
