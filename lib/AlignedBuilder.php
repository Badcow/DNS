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

namespace Badcow\DNS;

use Badcow\DNS\Rdata\A;
use Badcow\DNS\Rdata\AAAA;
use Badcow\DNS\Rdata\APL;
use Badcow\DNS\Rdata\CNAME;
use Badcow\DNS\Rdata\DNAME;
use Badcow\DNS\Rdata\HINFO;
use Badcow\DNS\Rdata\LOC;
use Badcow\DNS\Rdata\MX;
use Badcow\DNS\Rdata\NS;
use Badcow\DNS\Rdata\PTR;
use Badcow\DNS\Rdata\RdataInterface;
use Badcow\DNS\Rdata\SOA;
use Badcow\DNS\Rdata\SRV;
use Badcow\DNS\Rdata\TXT;

class AlignedBuilder
{
    const COMMENT_DELIMINATOR = '; ';

    const MULTILINE_BEGIN = '(';

    const MULTILINE_END = ')';

    /**
     * The order in which Resource Records should appear in a zone.
     *
     * @var array
     */
    private static $order = [
        SOA::TYPE,
        NS::TYPE,
        A::TYPE,
        AAAA::TYPE,
        CNAME::TYPE,
        DNAME::TYPE,
        MX::TYPE,
        LOC::TYPE,
        HINFO::TYPE,
        TXT::TYPE,
        PTR::TYPE,
        SRV::TYPE,
    ];

    /**
     * @param Zone $zone
     *
     * @return string
     */
    public static function build(Zone $zone): string
    {
        $master = self::generateControlEntries($zone);
        $resourceRecords = $zone->getResourceRecords();
        $current = SOA::TYPE;
        usort($resourceRecords, [__CLASS__, 'compareResourceRecords']);

        list($namePadding, $ttlPadding, $typePadding, $rdataPadding) = self::getPadding($zone);

        foreach ($resourceRecords as $resourceRecord) {
            $rdata = $resourceRecord->getRdata();
            if (null == $rdata) {
                continue;
            }

            if ($resourceRecord->getType() !== $current) {
                $master .= PHP_EOL.self::COMMENT_DELIMINATOR.$resourceRecord->getType().' RECORDS'.PHP_EOL;
                $current = $resourceRecord->getType();
            }

            $master .= sprintf('%s %s %s %s %s',
                str_pad((string) $resourceRecord->getName(), $namePadding, ' ', STR_PAD_RIGHT),
                str_pad((string) $resourceRecord->getTtl(), $ttlPadding, ' ', STR_PAD_RIGHT),
                str_pad((string) $resourceRecord->getClass(), 2, ' ', STR_PAD_RIGHT),
                str_pad($rdata->getType(), $typePadding, ' ', STR_PAD_RIGHT),
                self::generateRdataOutput($rdata, $rdataPadding)
            );

            $master .= self::generateComment($resourceRecord);
            $master .= PHP_EOL;
        }

        return $master;
    }

    private static function generateControlEntries(Zone $zone): string
    {
        $master = '$ORIGIN '.$zone->getName().PHP_EOL;
        if (null !== $zone->getDefaultTtl()) {
            $master .= '$TTL '.$zone->getDefaultTtl().PHP_EOL;
        }

        return $master;
    }

    private static function generateComment(ResourceRecord $resourceRecord): string
    {
        if (null !== $resourceRecord->getComment()) {
            return self::COMMENT_DELIMINATOR.$resourceRecord->getComment();
        }

        return '';
    }

    /**
     * Compares two ResourceRecords to determine which is the higher order. Used with the usort() function.
     *
     * @param ResourceRecord $a
     * @param ResourceRecord $b
     *
     * @return int
     */
    public static function compareResourceRecords(ResourceRecord $a, ResourceRecord $b): int
    {
        $a_rdata = (null === $a->getRdata()) ? '' : $a->getRdata()->toText();
        $b_rdata = (null === $b->getRdata()) ? '' : $b->getRdata()->toText();

        if ($a->getType() === $b->getType()) {
            return strcmp($a->getName().$a_rdata, $b->getName().$b_rdata);
        }

        $_a = array_search($a->getType(), self::$order);
        $_b = array_search($b->getType(), self::$order);

        if (is_int($_a) && is_int($_b)) {
            return $_a - $_b;
        }

        if (false === $_a) {
            return 1;
        }

        return -1;
    }

    /**
     * @param RdataInterface $rdata
     * @param int            $padding
     *
     * @return string
     */
    private static function generateRdataOutput(RdataInterface $rdata, int $padding): string
    {
        if ($rdata instanceof SOA) {
            return self::outputSoa($rdata, $padding);
        }

        if ($rdata instanceof APL) {
            return self::outputApl($rdata, $padding);
        }

        if ($rdata instanceof LOC) {
            return self::outputLoc($rdata, $padding);
        }

        return $rdata->toText();
    }

    /**
     * @param SOA $rdata
     * @param int $padding
     *
     * @return string
     */
    private static function outputSoa(SOA $rdata, int $padding): string
    {
        $vars = [
            $rdata->getMname(),
            $rdata->getRname(),
            $rdata->getSerial(),
            $rdata->getRefresh(),
            $rdata->getRetry(),
            $rdata->getExpire(),
            $rdata->getMinimum(),
        ];

        $longestVarLength = max(array_map('strlen', $vars));

        return self::MULTILINE_BEGIN.PHP_EOL.
        self::makeLine((string) $rdata->getMname(), 'MNAME', $longestVarLength, $padding).
        self::makeLine((string) $rdata->getRname(), 'RNAME', $longestVarLength, $padding).
        self::makeLine((string) $rdata->getSerial(), 'SERIAL', $longestVarLength, $padding).
        self::makeLine((string) $rdata->getRefresh(), 'REFRESH', $longestVarLength, $padding).
        self::makeLine((string) $rdata->getRetry(), 'RETRY', $longestVarLength, $padding).
        self::makeLine((string) $rdata->getExpire(), 'EXPIRE', $longestVarLength, $padding).
        self::makeLine((string) $rdata->getMinimum(), 'MINIMUM', $longestVarLength, $padding).
        str_repeat(' ', $padding).self::MULTILINE_END;
    }

    /**
     * @param APL $rdata
     * @param int $padding
     *
     * @return string
     */
    private static function outputApl(APL $rdata, int $padding): string
    {
        $blocks = explode(' ', $rdata->toText());
        $longestVarLength = max(array_map('strlen', $blocks));
        $string = self::MULTILINE_BEGIN.PHP_EOL;

        foreach ($blocks as $block) {
            $string .= self::makeLine($block, null, $longestVarLength, $padding);
        }

        return $string.str_repeat(' ', $padding).self::MULTILINE_END;
    }

    /**
     * @param LOC $rdata
     * @param int $padding
     *
     * @return string
     */
    private static function outputLoc(LOC $rdata, int $padding): string
    {
        $parts = [
            $rdata->getLatitude(LOC::FORMAT_DMS),
            $rdata->getLongitude(LOC::FORMAT_DMS),
            sprintf('%.2fm', $rdata->getAltitude()),
            sprintf('%.2fm', $rdata->getSize()),
            sprintf('%.2fm', $rdata->getHorizontalPrecision()),
            sprintf('%.2fm', $rdata->getVerticalPrecision()),
        ];

        $longestVarLength = max(array_map('strlen', $parts));

        return self::MULTILINE_BEGIN.PHP_EOL.
            self::makeLine((string) $rdata->getLatitude(LOC::FORMAT_DMS), 'LATITUDE', $longestVarLength, $padding).
            self::makeLine((string) $rdata->getLongitude(LOC::FORMAT_DMS), 'LONGITUDE', $longestVarLength, $padding).
            self::makeLine(sprintf('%.2fm', $rdata->getAltitude()), 'ALTITUDE', $longestVarLength, $padding).
            self::makeLine(sprintf('%.2fm', $rdata->getSize()), 'SIZE', $longestVarLength, $padding).
            self::makeLine(sprintf('%.2fm', $rdata->getHorizontalPrecision()), 'HORIZONTAL PRECISION', $longestVarLength, $padding).
            self::makeLine(sprintf('%.2fm', $rdata->getVerticalPrecision()), 'VERTICAL PRECISION', $longestVarLength, $padding).
            str_repeat(' ', $padding).self::MULTILINE_END;
    }

    /**
     * Returns a padded line with comment.
     *
     * @param string $text
     * @param string $comment
     * @param int    $longestVarLength
     * @param int    $padding
     *
     * @return string
     */
    private static function makeLine(string $text, ?string $comment, int $longestVarLength, int $padding): string
    {
        $output = str_repeat(' ', $padding).str_pad($text, $longestVarLength);

        if (null !== $comment) {
            $output .= ' '.self::COMMENT_DELIMINATOR.$comment;
        }

        return $output.PHP_EOL;
    }

    /**
     * Get the padding required for a zone.
     *
     * @param Zone $zone
     *
     * @return int[] Array order: name, ttl, type, rdata
     */
    private static function getPadding(Zone $zone): array
    {
        $name = $ttl = $type = 0;

        foreach ($zone as $resourceRecord) {
            $name = max($name, strlen($resourceRecord->getName() ?? ''));
            $ttl = max($ttl, strlen((string) $resourceRecord->getTtl()));
            $type = max($type, strlen($resourceRecord->getType() ?? ''));
        }

        return [
            $name,
            $ttl,
            $type,
            $name + $ttl + $type + 6,
        ];
    }
}
