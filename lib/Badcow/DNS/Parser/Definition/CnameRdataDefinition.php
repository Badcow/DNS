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
use Badcow\DNS\Rdata\CnameRdata;

/**
 * Class CnameRdataDefinition
 * @package Badcow\DNS\Parser\Definition\Rdata
 * @TODO Complete this
 */
class CnameRdataDefinition implements DefinitionInterface
{
    /**
     * @constant
     */
    const FQDN_PATTERN = '/(?:(?=^.{1,254}$)(^(?:(?!\d+\.)[a-zA-Z0-9_\-]{1,63}\.?)+(?:[a-zA-Z]{2,})\.$))|(?:^@$)/';

    /**
     * @constant
     */
    const UQDN_PATTERN = '/(?:(?=^.{1,254}$)(^(?:(?!\d+\.)[a-zA-Z0-9_\-]{1,63}\.?)+(?:[a-zA-Z]{2,})))|(?:^@$)/';

    /**
     * {@inheritdoc}
     */
    public function isValid($data)
    {
        return (preg_match('CNAME', $data) === 1);
    }

    /**
     * {@inheritdoc}
     */
    public function parse($data)
    {
        return new CnameRdata;
    }
}