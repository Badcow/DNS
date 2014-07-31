<?php
/*
 * This file is part of the Membership Database.
 *
 * (c) Samuel Williams <sam@swilliams.com.au>
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
