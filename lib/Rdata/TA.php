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

/**
 * {@link http://www.watson.org/~weiler/INI1999-19.pdf}.
 */
class TA extends DS
{
    public const TYPE = 'TA';
    public const TYPE_CODE = 32768;
}
