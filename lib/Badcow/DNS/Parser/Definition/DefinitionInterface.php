<?php
/*
 * This file is part of Badcow DNS Library.
 *
 * (c) Samuel Williams <sam@badcow.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Badcow\DNS\Parser\Definition;

interface DefinitionInterface
{
    /**
     * Determine if data is a valid Rdata type
     *
     * @param string $data
     * @return bool
     */
    public function isValid($data);

    /**
     * Parse some given data and return the populated
     * RdataInterface instance
     *
     * @param $data
     * @return \Badcow\DNS\Rdata\RdataInterface
     * @throws \Badcow\DNS\Parser\ParseException
     */
    public function parse($data);
}