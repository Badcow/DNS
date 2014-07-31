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

use Badcow\DNS\Rdata\ARdata;
use Badcow\DNS\Parser\ParseException;

class ARdataDefinition implements DefinitionInterface
{
    const A_RDATA_VALID_REGEX = '/A\s+(?:[0-9]+\s+)?(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/';

    const A_RDATA_REGEX = '/(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/';

    /**
     * {@inheritdoc}
     */
    public function isValid($data)
    {
        return (preg_match(self::A_RDATA_VALID_REGEX, $data) === 1);
    }

    /**
     * {@inheritdoc}
     */
    public function parse($data)
    {
        if (preg_match(self::A_RDATA_REGEX, $data, $matches) !== 1) {
            throw new ParseException(sprintf('Unable to parse data "%"', $data));
        }

        $aRdata = new ARdata;
        $aRdata->setAddress($matches[0]);

        return $aRdata;
    }
}