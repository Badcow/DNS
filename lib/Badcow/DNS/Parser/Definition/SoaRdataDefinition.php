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

use Badcow\DNS\Parser\ParseException;
use Badcow\DNS\Rdata\SoaRdata;

/**
 * Class CnameRdataDefinition
 * @package Badcow\DNS\Parser\Definition\Rdata
 * @TODO Complete this
 */
class SoaRdataDefinition implements DefinitionInterface
{
    /**
     * {@inheritdoc}
     */
    public function isValid($data)
    {
        return (preg_match('SOA', $data) === 1);
    }

    /**
     * {@inheritdoc}
     */
    public function parse($data)
    {
        return new SoaRdata;
    }
}