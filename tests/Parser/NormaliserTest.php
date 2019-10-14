<?php

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
     * @throws \Badcow\DNS\Parser\ParseException|\Exception
     */
    public function testRemovesComments()
    {
        $zone = self::readFile(__DIR__.'/Resources/testClearComments_sample.txt');
        $expectation = self::readFile(__DIR__.'/Resources/testClearComments_expectation.txt');

        $this->assertEquals($expectation, Normaliser::normalise($zone));
    }

    /**
     * Multi-line records collapse onto single line.
     *
     * @throws \Badcow\DNS\Parser\ParseException|\Exception
     */
    public function testMultilineRecordsCollapseOntoSingleLine()
    {
        $zone = self::readFile(__DIR__.'/Resources/testCollapseMultilines_sample.txt');
        $expectation = self::readFile(__DIR__.'/Resources/testCollapseMultilines_expectation.txt');

        $this->assertEquals($expectation, Normaliser::normalise($zone));
    }

    /**
     * Unbalanced brackets cause ParseException.
     *
     * @expectedException \Badcow\DNS\Parser\ParseException
     * @expectedExceptionMessage End of file reached. Unclosed bracket.
     *
     * @throws \Badcow\DNS\Parser\ParseException
     */
    public function testUnbalancedBracketsCauseParseException()
    {
        Normaliser::normalise($this->unbalancedBrackets);
    }

    /**
     * Unbalanced quotation marks cause ParseException.
     *
     * @expectedException \Badcow\DNS\Parser\ParseException
     * @expectedExceptionMessage Unbalanced double quotation marks. End of file reached.
     *
     * @throws \Badcow\DNS\Parser\ParseException
     */
    public function testUnbalancedQuotationMarksCauseParseException()
    {
        $string = 'mail IN TXT "Some string';
        Normaliser::normalise($string);
    }

    /**
     * Line feed inside quotation marks cause exception.
     *
     * @expectedException \Badcow\DNS\Parser\ParseException
     * @expectedExceptionMessage Line Feed found within double quotation marks context. [Line no: 2]
     *
     * @throws \Badcow\DNS\Parser\ParseException
     */
    public function testLineFeedInsideQuotationMarksCauseException()
    {
        $string = "www IN CNAME @\n     mail IN TXT \"Some \nstring\"";
        Normaliser::normalise($string);
    }

    /**
     * @throws \Exception
     */
    public function testCommentsAreRetained()
    {
        $zone = self::readFile(__DIR__.'/Resources/testClearComments_sample.txt');
        $expectation = self::readFile(__DIR__.'/Resources/testKeepComments_expectation.txt');
        $normalisedZone = Normaliser::normalise($zone, true);

        $this->assertEquals($expectation, $normalisedZone);
    }

    /**
     * @throws \Exception
     */
    public function testMultilineCommentsAreRetained()
    {
        $zone = self::readFile(__DIR__.'/Resources/testCollapseMultilines_sample.txt');
        $expectation = self::readFile(__DIR__.'/Resources/testCollapseMultilinesWithComments_expectation.txt');
        $normalisedZone = Normaliser::normalise($zone, Comments::END_OF_ENTRY | Comments::MULTILINE | Comments::ORPHAN);

        $this->assertEquals($expectation, $normalisedZone);
    }

    /**
     * @throws \Exception
     */
    public function testMultilineTxtRecords()
    {
        $zone = self::readFile(__DIR__.'/Resources/testMultilineTxtRecords_sample.txt');
        $expectation = self::readFile(__DIR__.'/Resources/testMultilineTxtRecords_expectation.txt');
        $normalisedZone = Normaliser::normalise($zone, true);

        $this->assertEquals($expectation, $normalisedZone);
    }

    /**
     * @throws \Exception
     */
    public function testKeepCommentsWithoutLinefeedAtEnd()
    {
        $zone = self::readFile(__DIR__.'/Resources/testKeepCommentsWithoutLinefeedAtEnd_sample.txt');
        $expectation = self::readFile(__DIR__.'/Resources/testKeepCommentsWithoutLinefeedAtEnd_expectation.txt');
        $normalisedZone = Normaliser::normalise($zone, true);

        $this->assertEquals($expectation, $normalisedZone);
    }

    /**
     * @throws \Exception
     */
    public function testCommentOptions()
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
     * @param string $filename
     *
     * @return string
     *
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
