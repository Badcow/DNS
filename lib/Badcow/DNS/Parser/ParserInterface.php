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

use Badcow\DNS\Parser\Definition\DefinitionInterface;
use Badcow\DNS\ZoneInterface;

interface ParserInterface
{
    /**
     * @param string $zoneName
     * @param string $zone
     * @return ZoneInterface mixed
     */
    public function parse($zoneName, $zone);

    /**
     * @param DefinitionInterface $definition
     */
    public function addDefinition(DefinitionInterface $definition);
}