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

namespace Badcow\DNS\Parser;

/**
 * Interface ZoneFileFetcherInterface: used to get the contents of other zone files that are stated in the $INCLUDE directive.
 */
interface ZoneFileFetcherInterface
{
    /**
     * Fetches the contents of a zone file with a given path.
     *
     * @param string $path the path, relative or otherwise, to a zone file
     *
     * @return string the text contents of the zone file
     */
    public function fetch(string $path): string;
}
