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

namespace Badcow\DNS\Validator;

use Badcow\DNS\ResourceRecord;
use Badcow\DNS\Zone;

class PTRValidator extends AbstractValidator
{
    /**
     * {@inheritdoc}
     */
    public static function canAddToZone(Zone $zone, ResourceRecord $record): bool
    {
        return self::noCNAMEinZone($zone, $record) && self::noDuplicate($zone, $record);
    }
}
