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

namespace Badcow\DNS\Edns\Option;

use Badcow\DNS\Rdata;

class DecodeException extends \Exception
{
    public function __construct(string $option, string $value, int $code = 0, \Throwable $previous = null)
    {
        $message = sprintf('Unable to decode %s option from binary data "%s"', $option, Rdata\DecodeException::binaryToHex($value));
        parent::__construct($message, $code, $previous);
    }
}
