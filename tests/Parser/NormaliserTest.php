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

namespace Badcow\DNS\Tests\Parser;

use Badcow\DNS\Parser\Comments;
use Badcow\DNS\Parser\Normaliser;
use Badcow\DNS\Parser\ParseException;
use PHPUnit\Framework\TestCase;

class NormaliserTest extends TestCase
{
    /**
     * @var string
     */
    private $unbalancedBrackets = <<< TXT
example.com. IN SOA (
                     example.com.       ; MNAME
                     post.example.com.  ; RNAME
                     2014110501         ; SERIAL
                     3600               ; REFRESH
                     14400              ; RETRY
                     604800             ; EXPIRE
                     3600               ; MINIMUM
TXT;

    /**
     * @var string
     */
    private $commentsOptionsSample = <<< TXT
 ; SOA Record
example.com. IN SOA (
                     example.com.       ; MNAME
                     post.example.com.  ; RNAME
                     2014110501         ; SERIAL
                     3600               ; REFRESH
                     14400              ; RETRY
                     604800             ; EXPIRE
                     3600               ; MINIMUM
                     );This is a Start of Authority
TXT;

    /**
     * @throws ParseException|\Exception
     */
    public function testRemovesComments(): void
    {
        $zone = self::readFile(__DIR__.'/Resources/testClearComments_sample.txt');
        $expectation = self::readFile(__DIR__.'/Resources/testClearComments_expectation.txt');

        $this->assertEquals($expectation, Normaliser::normalise($zone));
    }

    /**
     * Multi-line records collapse onto single line.
     *
     * @throws ParseException|\Exception
     */
    public function testMultilineRecordsCollapseOntoSingleLine(): void
    {
        $zone = self::readFile(__DIR__.'/Resources/testCollapseMultilines_sample.txt');
        $expectation = self::readFile(__DIR__.'/Resources/testCollapseMultilines_expectation.txt');

        $this->assertEquals($expectation, Normaliser::normalise($zone));
    }

    /**
     * Unbalanced brackets cause ParseException.
     *
     * @throws ParseException
     */
    public function testUnbalancedBracketsCauseParseException(): void
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('End of file reached. Unclosed bracket.');
        Normaliser::normalise($this->unbalancedBrackets);
    }

    /**
     * Unbalanced quotation marks cause ParseException.
     *
     * @throws ParseException
     */
    public function testUnbalancedQuotationMarksCauseParseException(): void
    {
        $string = 'mail IN TXT "Some string';

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Unbalanced double quotation marks. End of file reached.');
        Normaliser::normalise($string);
    }

    /**
     * Line feed inside quotation marks cause exception.
     *
     * @throws ParseException
     */
    public function testLineFeedInsideQuotationMarksCauseException(): void
    {
        $string = "www IN CNAME @\n     mail IN TXT \"Some \nstring\"";

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Line Feed found within double quotation marks context. [Line no: 2]');
        Normaliser::normalise($string);
    }

    /**
     * @throws \Exception
     */
    public function testCommentsAreRetained(): void
    {
        $zone = self::readFile(__DIR__.'/Resources/testClearComments_sample.txt');
        $expectation = self::readFile(__DIR__.'/Resources/testKeepComments_expectation.txt');
        $normalisedZone = Normaliser::normalise($zone, Comments::END_OF_ENTRY);

        $this->assertEquals($expectation, $normalisedZone);
    }

    /**
     * @throws \Exception
     */
    public function testMultilineCommentsAreRetained(): void
    {
        $zone = self::readFile(__DIR__.'/Resources/testCollapseMultilines_sample.txt');
        $expectation = self::readFile(__DIR__.'/Resources/testCollapseMultilinesWithComments_expectation.txt');
        $normalisedZone = Normaliser::normalise($zone, Comments::END_OF_ENTRY | Comments::MULTILINE | Comments::ORPHAN);

        $this->assertEquals($expectation, $normalisedZone);
    }

    /**
     * @throws \Exception
     */
    public function testMultilineTxtRecords(): void
    {
        $zone = self::readFile(__DIR__.'/Resources/testMultilineTxtRecords_sample.txt');
        $expectation = self::readFile(__DIR__.'/Resources/testMultilineTxtRecords_expectation.txt');
        $normalisedZone = Normaliser::normalise($zone, Comments::END_OF_ENTRY);

        $this->assertEquals($expectation, $normalisedZone);
    }

    /**
     * @throws \Exception
     */
    public function testKeepCommentsWithoutLinefeedAtEnd(): void
    {
        $zone = self::readFile(__DIR__.'/Resources/testKeepCommentsWithoutLinefeedAtEnd_sample.txt');
        $expectation = self::readFile(__DIR__.'/Resources/testKeepCommentsWithoutLinefeedAtEnd_expectation.txt');
        $normalisedZone = Normaliser::normalise($zone, Comments::END_OF_ENTRY);

        $this->assertEquals($expectation, $normalisedZone);
    }

    /**
     * @throws \Exception
     */
    public function testCommentOptions(): void
    {
        $option_1 = Comments::END_OF_ENTRY;
        $expectation_1 = 'example.com. IN SOA example.com. post.example.com. 2014110501 3600 14400'.
            ' 604800 3600;This is a Start of Authority';

        $option_2 = Comments::MULTILINE;
        $expectation_2 = 'example.com. IN SOA example.com. post.example.com. 2014110501 3600 14400'.
            ' 604800 3600;MNAME RNAME SERIAL REFRESH RETRY EXPIRE MINIMUM';

        $option_3 = Comments::END_OF_ENTRY | Comments::MULTILINE;
        $expectation_3 = 'example.com. IN SOA example.com. post.example.com. 2014110501 3600 14400'.
            ' 604800 3600;MNAME RNAME SERIAL REFRESH RETRY EXPIRE MINIMUMThis is a Start of Authority';

        $option_4 = Comments::ORPHAN;
        $expectation_4 = ";SOA Record\nexample.com. IN SOA example.com. post.example.com. 2014110501 3600 14400".
            ' 604800 3600';

        $option_5 = Comments::ORPHAN | Comments::END_OF_ENTRY;
        $expectation_5 = ";SOA Record\nexample.com. IN SOA example.com. post.example.com. 2014110501 3600 14400".
            ' 604800 3600;This is a Start of Authority';

        $option_6 = Comments::ORPHAN | Comments::MULTILINE;
        $expectation_6 = ";SOA Record\nexample.com. IN SOA example.com. post.example.com. 2014110501 3600 14400".
            ' 604800 3600;MNAME RNAME SERIAL REFRESH RETRY EXPIRE MINIMUM';

        $option_7 = Comments::ALL;
        $expectation_7 = ";SOA Record\nexample.com. IN SOA example.com. post.example.com. 2014110501 3600 14400".
            ' 604800 3600;MNAME RNAME SERIAL REFRESH RETRY EXPIRE MINIMUMThis is a Start of Authority';

        $this->assertEquals($expectation_1, Normaliser::normalise($this->commentsOptionsSample, $option_1));
        $this->assertEquals($expectation_2, Normaliser::normalise($this->commentsOptionsSample, $option_2));
        $this->assertEquals($expectation_3, Normaliser::normalise($this->commentsOptionsSample, $option_3));
        $this->assertEquals($expectation_4, Normaliser::normalise($this->commentsOptionsSample, $option_4));
        $this->assertEquals($expectation_5, Normaliser::normalise($this->commentsOptionsSample, $option_5));
        $this->assertEquals($expectation_6, Normaliser::normalise($this->commentsOptionsSample, $option_6));
        $this->assertEquals($expectation_7, Normaliser::normalise($this->commentsOptionsSample, $option_7));
    }

    /**
     * @throws \Exception
     */
    public static function readFile(string $filename): string
    {
        $file = file_get_contents($filename);

        if (false === $file) {
            throw new \Exception(sprintf('Unable to read file "%s".', $filename));
        }

        //Remove Windows carriage returns.
        $file = str_replace("\r", '', $file);

        return $file;
    }
}
