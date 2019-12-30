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

use Badcow\DNS\Parser\Tokens;
use Badcow\DNS\Rdata\APL;
use Badcow\DNS\Rdata\LOC;
use Badcow\DNS\Rdata\RRSIG;
use Badcow\DNS\Rdata\SOA;

class AlignedRdataFormatters
{
    private function __construct()
    {
    }

    /**
     * @return callable[]
     */
    public static function getRdataFormatters(): array
    {
        return [
            SOA::TYPE => __CLASS__.'::SOA',
            APL::TYPE => __CLASS__.'::APL',
            LOC::TYPE => __CLASS__.'::LOC',
            RRSIG::TYPE => __CLASS__.'::RRSIG',
        ];
    }

    /**
     * @param SOA $rdata
     * @param int $padding
     *
     * @return string
     */
    public static function SOA(SOA $rdata, int $padding): string
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

        return Tokens::OPEN_BRACKET.Tokens::LINE_FEED.
            self::makeLine((string) $rdata->getMname(), 'MNAME', $longestVarLength, $padding).
            self::makeLine((string) $rdata->getRname(), 'RNAME', $longestVarLength, $padding).
            self::makeLine((string) $rdata->getSerial(), 'SERIAL', $longestVarLength, $padding).
            self::makeLine((string) $rdata->getRefresh(), 'REFRESH', $longestVarLength, $padding).
            self::makeLine((string) $rdata->getRetry(), 'RETRY', $longestVarLength, $padding).
            self::makeLine((string) $rdata->getExpire(), 'EXPIRE', $longestVarLength, $padding).
            self::makeLine((string) $rdata->getMinimum(), 'MINIMUM', $longestVarLength, $padding).
            str_repeat(' ', $padding).Tokens::CLOSE_BRACKET;
    }

    /**
     * @param APL $rdata
     * @param int $padding
     *
     * @return string
     */
    public static function APL(APL $rdata, int $padding): string
    {
        $blocks = explode(' ', $rdata->toText());
        $longestVarLength = max(array_map('strlen', $blocks));
        $string = Tokens::OPEN_BRACKET.Tokens::LINE_FEED;

        foreach ($blocks as $block) {
            $string .= self::makeLine($block, null, $longestVarLength, $padding);
        }

        return $string.str_repeat(' ', $padding).Tokens::CLOSE_BRACKET;
    }

    /**
     * Splits the RRSIG Signature into 32 character chunks.
     *
     * @param RRSIG $rrsig
     * @param int   $padding
     *
     * @return string
     */
    public static function RRSIG(RRSIG $rrsig, int $padding): string
    {
        $strPadding = str_repeat(Tokens::SPACE, $padding);
        $signatureParts = str_split($rrsig->getSignature(), 32);

        $rdata = $rrsig->getTypeCovered().Tokens::SPACE.
            $rrsig->getAlgorithm().Tokens::SPACE.
            $rrsig->getLabels().Tokens::SPACE.
            $rrsig->getOriginalTtl().Tokens::SPACE.Tokens::OPEN_BRACKET.Tokens::LINE_FEED.
            $strPadding.
            $rrsig->getSignatureExpiration()->format(RRSIG::TIME_FORMAT).Tokens::SPACE.
            $rrsig->getSignatureInception()->format(RRSIG::TIME_FORMAT).Tokens::SPACE.
            $rrsig->getKeyTag().Tokens::SPACE.
            $rrsig->getSignersName();

        foreach ($signatureParts as $line) {
            $rdata .= Tokens::LINE_FEED.$strPadding.$line;
        }

        $rdata .= Tokens::SPACE.Tokens::CLOSE_BRACKET;

        return $rdata;
    }

    /**
     * @param LOC $rdata
     * @param int $padding
     *
     * @return string
     */
    public static function LOC(LOC $rdata, int $padding): string
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

        return Tokens::OPEN_BRACKET.Tokens::LINE_FEED.
            self::makeLine((string) $rdata->getLatitude(LOC::FORMAT_DMS), 'LATITUDE', $longestVarLength, $padding).
            self::makeLine((string) $rdata->getLongitude(LOC::FORMAT_DMS), 'LONGITUDE', $longestVarLength, $padding).
            self::makeLine(sprintf('%.2fm', $rdata->getAltitude()), 'ALTITUDE', $longestVarLength, $padding).
            self::makeLine(sprintf('%.2fm', $rdata->getSize()), 'SIZE', $longestVarLength, $padding).
            self::makeLine(sprintf('%.2fm', $rdata->getHorizontalPrecision()), 'HORIZONTAL PRECISION', $longestVarLength, $padding).
            self::makeLine(sprintf('%.2fm', $rdata->getVerticalPrecision()), 'VERTICAL PRECISION', $longestVarLength, $padding).
            str_repeat(' ', $padding).Tokens::CLOSE_BRACKET;
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
    public static function makeLine(string $text, ?string $comment, int $longestVarLength, int $padding): string
    {
        $output = str_repeat(Tokens::SPACE, $padding).str_pad($text, $longestVarLength);

        if (null !== $comment) {
            $output .= Tokens::SPACE.Tokens::SEMICOLON.Tokens::SPACE.$comment;
        }

        return $output.Tokens::LINE_FEED;
    }
}
