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

use Badcow\DNS\Parser\Interpreter;

class InterpreterTest extends \PHPUnit_Framework_TestCase
{
    public function testStripCommentFromLine()
    {
        $test_data = 'www IN CNAME example.com ; This is a comment that needs to be stripped';
        $empty_comment = 'example.com IN A 255.255.255.255';

        $stripped = Interpreter::stripCommentFromLine($test_data);
        $this->assertEquals('www IN CNAME example.com', $stripped[0]);
        $this->assertEquals('This is a comment that needs to be stripped', $stripped[1]);

        $stripped = Interpreter::stripCommentFromLine($empty_comment);
        $this->assertEquals('example.com IN A 255.255.255.255', $stripped[0]);
        $this->assertEquals('', $stripped[1]);
    }

    public function testGetResourceNameFromLine()
    {
        $test_data1 = 'www IN CNAME example.com ; This is a comment that needs to be stripped';
        $test_data2 = ' @ IN A 255.255.255.255; Comments';
        $test_data3 = '           1800 A        192.168.1.5';

        $this->assertEquals('www', Interpreter::getResourceNameFromLine($test_data1));
        $this->assertEquals('@',   Interpreter::getResourceNameFromLine($test_data2));
        $this->assertEquals('',    Interpreter::getResourceNameFromLine($test_data3));
    }

    public function testGetClassFromLine()
    {
        $test_data1 = 'www IN CNAME example.com ; This is a comment that needs to be stripped';
        $test_data2 = ' @        A 255.255.255.255; Comments';
        $test_data3 = '@   HS  TXT  "This is some data"';
        $test_data4 = " IN\tCH  TXT  \"This is some data\"";
        $test_data5 = 'www IM CNAME example.com; Comments ';

        $this->assertEquals('IN', Interpreter::getClassFromLine($test_data1));
        $this->assertEquals(null, Interpreter::getClassFromLine($test_data2));
        $this->assertEquals('HS', Interpreter::getClassFromLine($test_data3));
        $this->assertEquals('CH', Interpreter::getClassFromLine($test_data4));
        $this->assertEquals(null, Interpreter::getClassFromLine($test_data5));
    }

    public function testExpand()
    {
        $test_data = <<<TXT
example.com. IN SOA example.com. (
     postmaster.example.com. ; mname
     2013011601              ; the serial number
     10800                   ; Refresh in seconds
     3600                    ; Retry in seconds
     604800                  ; Expire in seconds
     38400 )                 ; Minimum TTL

www IN CNAME example.com ; This is a comment that needs to be stripped
@   A 1800     255.255.255.255; Comments
TXT;

        $expectation = array(
            array(
                'line' => 'example.com. IN SOA example.com. postmaster.example.com. 2013011601 10800 3600 604800 38400',
                'comment' => 'mname the serial number Refresh in seconds Retry in seconds Expire in seconds Minimum TTL',
            ),
            array(
                'line' => 'www IN CNAME example.com',
                'comment' => 'This is a comment that needs to be stripped',
            ),
            array(
                'line' => '@ A 1800 255.255.255.255',
                'comment' => 'Comments',
            ),
        );

        $lines = Interpreter::expand($test_data);

        $this->assertEquals($expectation, $lines);
    }
}