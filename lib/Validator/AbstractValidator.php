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

use Badcow\DNS\Rdata\CNAME;
use Badcow\DNS\ResourceRecord;
use Badcow\DNS\Zone;

abstract class AbstractValidator
{
    /**
     * Test jf the the $record can be added to the $zone.
     *
     * @param Zone           $zone
     * @param ResourceRecord $record
     *
     * @return bool
     */
    abstract public static function canAddToZone(Zone $zone, ResourceRecord $record): bool;

    /**
     * Ensure $zone does not contain existing CNAME alias corresponding to $record's name.
     *
     * E.g.
     *      www IN CNAME example.com.
     *      www IN TXT "This is a violation of DNS specifications."
     *
     * @see https://tools.ietf.org/html/rfc1034#section-3.6.2
     *
     * @param Zone           $zone
     * @param ResourceRecord $record
     *
     * @return bool
     */
    public static function noCNAMEinZone(Zone $zone, ResourceRecord $record): bool
    {
        foreach ($zone as $rr) {
            if (CNAME::TYPE === $rr->getType()
                && $record->getName() === $rr->getName()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Ensure $zone does not contain existing record corresponding to $record's name and type.
     *
     * @param Zone           $zone
     * @param ResourceRecord $record
     *
     * @return bool
     */
    public static function noDuplicate(Zone $zone, ResourceRecord $record): bool
    {
        foreach ($zone as $rr) {
            if ($record->getType() === $rr->getType()
                && $record->getName() === $rr->getName()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Ensure $zone does not contain existing record corresponding to $record's name only.
     *
     * @param Zone           $zone
     * @param ResourceRecord $record
     *
     * @return bool
     */
    public static function nameDoesntExists(Zone $zone, ResourceRecord $record): bool
    {
        foreach ($zone as $rr) {
            if ($record->getName() === $rr->getName()) {
                return false;
            }
        }

        return true;
    }
}
