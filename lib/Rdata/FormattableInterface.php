<?php

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
 * Interface FormattableInterface.
 *
 * Indicates if there is a "nicer" way of outputting RData
 * so that it is more human readable
 */
interface FormattableInterface
{
    /**
     * Set the amount of left padding on the output.
     *
     * @param int $padding
     */
    public function setPadding($padding);

    /**
     * Outputs the RData in a much more human readable way.
     *
     * @return string
     */
    public function outputFormatted();
}
