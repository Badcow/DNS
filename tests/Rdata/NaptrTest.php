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

namespace Badcow\DNS\Tests\Rdata;

use Badcow\DNS\Rdata\Factory;
use Badcow\DNS\Rdata\NAPTR;
use PHPUnit\Framework\TestCase;

class NaptrTest extends TestCase
{
    public function getDataProvider(): array
    {
        return [
            //Text                                                 Order Pref Flags Service             Regexp                                 Replacement
            ['100 50 "s" "http+N2L+N2C+N2R" "" www.example.com.',  100,  50,  's',  'http+N2L+N2C+N2R', '',                                    'www.example.com.'],
            ['100 10 "" "" "!^urn:cid:.+@([^\.]+\.)(.*)$!\2!i" .', 100,  10,  '',   '',                 '!^urn:cid:.+@([^\.]+\.)(.*)$!\2!i',   '.'],
            ['100 50 "s" "SIP+D2U" "" _sip2._udp.testnaptr.at.',   100,  50,  's',  'SIP+D2U',          '',                                    '_sip2._udp.testnaptr.at.'],
            ['100 10 "u" "sip+E2U" "!^.*$!sip:information@foo.se!i" .', 100, 10, 'u', 'sip+E2U',        '!^.*$!sip:information@foo.se!i',      '.'],
        ];
    }

    public function testGetType(): void
    {
        $naptr = new NAPTR();
        $this->assertEquals('NAPTR', $naptr->getType());
    }

    public function testGetTypeCode(): void
    {
        $naptr = new NAPTR();
        $this->assertEquals(35, $naptr->getTypeCode());
    }

    /**
     * @dataProvider getDataProvider
     */
    public function testToText(string $text, int $order, int $preference, string $flags, string $services, string $regexp, string $replacement): void
    {
        $naptr = new NAPTR();
        $naptr->setOrder($order);
        $naptr->setPreference($preference);
        $naptr->setFlags($flags);
        $naptr->setServices($services);
        $naptr->setRegexp($regexp);
        $naptr->setReplacement($replacement);

        $this->assertEquals($text, $naptr->toText());
    }

    /**
     * @dataProvider getDataProvider
     */
    public function testToAndFromWire(string $text, int $order, int $preference, string $flags, string $services, string $regexp, string $replacement): void
    {
        $naptr = new NAPTR();
        $naptr->setOrder($order);
        $naptr->setPreference($preference);
        $naptr->setFlags($flags);
        $naptr->setServices($services);
        $naptr->setRegexp($regexp);
        $naptr->setReplacement($replacement);
        $wireFormat = $naptr->toWire();
        $rdLength = strlen($wireFormat);
        $wireFormat = 'abc'.$wireFormat;
        $offset = 3;

        $fromWire = new NAPTR();
        $fromWire->fromWire($wireFormat, $offset, $rdLength);
        $this->assertEquals($naptr, $fromWire);
        $this->assertEquals(3 + $rdLength, $offset);
    }

    /**
     * @dataProvider getDataProvider
     */
    public function testFromText(string $text, int $order, int $preference, string $flags, string $services, string $regexp, string $replacement): void
    {
        $naptr = new NAPTR();
        $naptr->fromText($text);

        $this->assertEquals($order, $naptr->getOrder());
        $this->assertEquals($preference, $naptr->getPreference());
        $this->assertEquals($flags, $naptr->getFlags());
        $this->assertEquals($services, $naptr->getServices());
        $this->assertEquals($regexp, $naptr->getRegexp());
        $this->assertEquals($replacement, $naptr->getReplacement());
    }

    /**
     * @dataProvider getDataProvider
     */
    public function testFactory(string $text, int $order, int $preference, string $flags, string $services, string $regexp, string $replacement): void
    {
        $naptr = Factory::NAPTR($order, $preference, $flags, $services, $regexp, $replacement);

        $this->assertEquals($order, $naptr->getOrder());
        $this->assertEquals($preference, $naptr->getPreference());
        $this->assertEquals($flags, $naptr->getFlags());
        $this->assertEquals($services, $naptr->getServices());
        $this->assertEquals($regexp, $naptr->getRegexp());
        $this->assertEquals($replacement, $naptr->getReplacement());
    }
}
