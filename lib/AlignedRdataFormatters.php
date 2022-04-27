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
use Badcow\DNS\Rdata\TXT;

class AlignedRdataFormatters
{
    /**
     * @var callable[]
     */
    public static $rdataFormatters = [
        SOA::TYPE => __CLASS__.'::SOA',
        APL::TYPE => __CLASS__.'::APL',
        LOC::TYPE => __CLASS__.'::LOC',
        RRSIG::TYPE => __CLASS__.'::RRSIG',
        TXT::TYPE => __CLASS__.'::TXT',
    ];

    /**
     * @return callable[]
     */
    public static function getRdataFormatters(): array
    {
        return self::$rdataFormatters;
    }

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

        /** @var callable $callable */
        $callable = '\strlen';
        $longestVarLength = max(array_map($callable, $vars));

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

    public static function APL(APL $rdata, int $padding): string
    {
        $blocks = explode(' ', $rdata->toText());
        $longestVarLength = (int) max(array_map('strlen', $blocks));
        $string = Tokens::OPEN_BRACKET.Tokens::LINE_FEED;

        foreach ($blocks as $block) {
            $string .= self::makeLine($block, null, $longestVarLength, $padding);
        }

        return $string.str_repeat(' ', $padding).Tokens::CLOSE_BRACKET;
    }

    /**
     * Split the TXT string into 40 character lines if the string is larger than 50 characters.
     */
    public static function TXT(TXT $txt, int $padding): string
    {
        if (strlen($txt->getText()) <= 50) {
            return $txt->toText();
        }

        $lines = str_split($txt->getText(), 40);
        $padString = str_repeat(Tokens::SPACE, $padding);

        $rdata = Tokens::OPEN_BRACKET.Tokens::SPACE;
        foreach ($lines as $line) {
            $txtSplit = new TXT();
            $txtSplit->setText($line);

            $rdata .= Tokens::LINE_FEED.$padString.Tokens::SPACE.Tokens::SPACE.$txtSplit->toText();
        }
        $rdata .= Tokens::LINE_FEED.$padString.Tokens::CLOSE_BRACKET;

        return $rdata;
    }

    /**
     * Splits the RRSIG Signature into 32 character chunks.
     */
    public static function RRSIG(RRSIG $rrsig, int $padding): string
    {
        $strPadding = str_repeat(Tokens::SPACE, $padding);
        $signatureParts = str_split(base64_encode($rrsig->getSignature()), 32);

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

    public static function LOC(LOC $loc, int $padding): string
    {
        $parts = [
            'LATITUDE' => (string) $loc->getLatitude(LOC::FORMAT_DMS),
            'LONGITUDE' => (string) $loc->getLongitude(LOC::FORMAT_DMS),
            'ALTITUDE' => sprintf('%.2fm', $loc->getAltitude()),
            'SIZE' => sprintf('%.2fm', $loc->getSize()),
            'HORIZONTAL PRECISION' => sprintf('%.2fm', $loc->getHorizontalPrecision()),
            'VERTICAL PRECISION' => sprintf('%.2fm', $loc->getVerticalPrecision()),
        ];

        $longestVarLength = max(array_map('strlen', $parts));
        $rdata = Tokens::OPEN_BRACKET.Tokens::LINE_FEED;

        foreach ($parts as $comment => $text) {
            $rdata .= self::makeLine($text, $comment, $longestVarLength, $padding);
        }
        $rdata .= str_repeat(Tokens::SPACE, $padding).Tokens::CLOSE_BRACKET;

        return $rdata;
    }

    /**
     * Returns a padded line with comment.
     *
     * @param string $comment
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
