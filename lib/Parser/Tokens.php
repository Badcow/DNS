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

class Tokens
{
    public const BACKSLASH = '\\';
    public const CARRIAGE_RETURN = "\r";
    public const CLOSE_BRACKET = ')';
    public const DOUBLE_QUOTES = '"';
    public const LINE_FEED = "\n";
    public const OPEN_BRACKET = '(';
    public const SEMICOLON = ';';
    public const SPACE = ' ';
    public const TAB = "\t";
}
