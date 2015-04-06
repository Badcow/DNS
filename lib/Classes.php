<?php
/*
 * This file is part of Badcow DNS Library.
 *
 * (c) Samuel Williams <sam@badcow.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Badcow\DNS;

class Classes
{
    const CHAOS = 'CH';
    const HESIOD = 'HS';
    const INTERNET = 'IN';

    /**
     * @var array
     */
    public static $classes = array(
        self::CHAOS    => 'CHAOS',
        self::HESIOD   => 'Hesiod',
        self::INTERNET => 'Internet',
    );
}
