<?php
/*
 * This file is part of Badcow DNS Library.
 *
 * (c) Samuel Williams <sam@badcow.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Badcow\DNS\Tests;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * Get an environment variable
     *
     * @param string $varname
     * @return null|string
     */
    protected function getEnvVariable($varname)
    {
        if (false !== $var = getenv($varname)) {
            return $var;
        }

        return null;
    }
}
 