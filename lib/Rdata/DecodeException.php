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

namespace Badcow\DNS\Rdata;

class DecodeException extends \Exception
{
    public function __construct(string $type, string $rdata, int $code = 0, \Throwable $previous = null)
    {
        $message = sprintf('Unable to decode %s record rdata from binary data "%s"', $type, self::binaryToHex($rdata));
        parent::__construct($message, $code, $previous);
    }

    /**
     * Convert a binary string into hexadecimal values.
     */
    public static function binaryToHex(string $rdata): string
    {
        $hex = array_map(function ($char) {
            return sprintf('0x%02x', ord($char));
        }, str_split($rdata));

        return implode(' ', $hex);
    }
}
