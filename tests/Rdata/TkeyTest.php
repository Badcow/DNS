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

use Badcow\DNS\Parser\Tokens;
use Badcow\DNS\Rdata\Factory;
use Badcow\DNS\Rdata\TKEY;
use PHPUnit\Framework\TestCase;

class TkeyTest extends TestCase
{
    private $dummyKeyData = <<<DATA
BAcIAwcAAAQGAQMGBwEFBwEGBAgGBwUFBwAIBwIDAwUEBgYIBQQBAwYIBQgFBQY
DAggDBgUCAgIBCAMDAwgDBQIFAggABggIAwMFBgABBwYDBAYHAwUEAAUIAgIICA
EHAAMIAAYBAAUEBggGAAMAAQMGAwAHAwUEAQQDBAgCCAAABQUAAQUCAAYEAAUHB
AMHAwYDAgQHBQEHAQcBAAQECAcEBQcHBwMBBAIIAQIGAAYCAAcDBwUIAQEBAwgI
BgQDBgECBQgGAQQGAQABAQUGAAUCBwQGAAQGCAUHBwMGAQcCAwcAAAIEAgECAQI
ECAgHBQQHAQgHBgAHCAIBAwUCAQMCAAEFBQMBAgcCAgIACAMECAEFCAUIBQEFAg
YGAgUIBQEHAggBCAcEBgMGAQYIAAAAAwQDAgUHCAUBAggDAgUAAAUBBwcBAwYEC
AIABAgIBwMEAggGAwUFAQAEBAQAAgIIBwgFBgMDCAABBggCAgcCBQYEBgIAAgIG
AwUFBAAFBQgGBwgGBwYBAQAGAQMHBwEICAYBBAMGAwEIBgIDAwgABAUCBwAIBAc
GAwcGBQEDAQcBBAMIAgUCAAUGAAgFBAQIBQcEBgAFAgUIAAcABggCAAcHBAgCBw
MIAAQIBwgBBAAIAAMDAQQBAwACAQUCAgQABgIICAMIBAAABgYBBAAAAwAICAUEA
QAFBAEFBQIGAQcHAAMECAMAAwUICAgFBQAHBgEBBgMCBgQABwUHCAACCAAGAgAC
BAAABAMHBQcEAAAFBwIECAUABwgEAAUGAggEBgAEBwMEAgAIBwEFAQIEBQcDAAI
FAgcBAgQGAgcGBwEFBgAGAQEFAwAAAgQBBAMHAAMCAgMGBggCAQgAAAgBCAQGCA
QFBgEAAQMGBgAFBwIEAgQHBQQDBAgHBQcDBAYDBAUAAAICBgADCAAECAcBBAEIA
QgABQcGAwAFCAMEAAEIAgYHBggCBggBAAYDAQIGCAgHBgUDBQYBAAEACAcCBwMC
BwAHAQYGCAIBAwUEBQEIAAUBBggCBQgHAAIGBwgGAwUFBQUDCAIHBwMCCAQIAAc
AAwEAAgcFAwQHBAIECAEABAMHAQcHBQQCBAcHBQEFCAYIBQUABAIHAAYIAAcEBQ
MHBQAGAQACBwAGBwcDAQYACAIABQcDBgcHAQUFBAEIBAgDBgcIAwMDAAAHCAAGA
gQAAQEFCAAGBwUABQACBgQHAAEIAwQHAQYCBAYGCAgDAwQBAQgDBgIAAAMCAwYB
AwcBAwQFBgQGAwEEBwYCAggDBQMBCAEFBAUIAAYABgIBBAgEAgAECAIHBwIAAAM
CAQAABAQICAgIAggBBQcIBgYBBAYCAgYGBwgIAgIDAQ==
DATA;

    private $dummyOtherData = <<<DATA
BwIHCAEHAgIIBAcIAAMEBQUIBgIGBgQDBQUDBAYCBQMDBAUBAQgGAAUAAwAAAQA
GBAEGAAMIBAEFBgEEAAQIAQEAAgMBBwQDCAUAAAIEBgUDAwAHBAEDAAMEBwUBAQ
gCAwUFAAQBBgcHAgMFCAABAgEFAgEAAwgFBQMHCAUGAwADAAIBBwcABggFAAUCB
wcIAQgCBgYDAQAEBgYGAgQIAwgBBggGAQABAwQCBwUIAAgEAwYABQcGBQMDBgQE
AwEAAQYAAAYIBAAGBwYIAgcFAwcFAAAGBwcAAggCBwQBAQIBCAUGAAAEBwYCAAQ
CAQMABgUAAAMFBgYECAEFAAUEAQgGAAADAwACBAYHBgAIAAABAQIABgACBQUHBQ
IFBwAIBwYEAwUBBAcIBQACAQUIAwYACAMDAgUGAAUFAAQCAAgCAwgDBgIHAgUIA
gMABwcEAQQEAwgIBQgCAQIIAwAHBQgFAwMGCAgGAAADBggHAAcIAgcBBwgIAQEE
AAYFCAAIBQUECAYBAgYACAcCAAAGAwIEAQEFCAcDAwIGBgcEBwAIBAYIBgEFBgM
DBgEACAcDAAEFCAQDAQQHBwgCAwIECAUHAgMFBgQDAggHAwMDBgUCCAYABwEBAw
MDAwQDBAUHAQMCBwgEAQEABgYIAQcGCAYIAgUABwIHAAAABQcIBAIBBwQHAQUDB
wADAwQDBQUABwQEBQEAAAIGAgMBBQUIBQYDAAcHAQUBBAIHBwYIBgUHAAYCAAIB
BgMFAggAAQQDAgQABAUIAAYEAgEIAQgIAwACAQgCBwEDBAIFBQgEAQYFAgECAAM
GCAgBBwEGAwcFBwcABgUGAgUFAgYEAAIFBgcEBwcGAQgDAAcBAgMDAQAHBQYCBQ
MBBAYBAgEBBgYFBgIFBQUDBwIDBQcCAwUHBwgDBQAEBwUFAQAFBAMCBwUDAwQCB
gAGBAACCAIFBgcABQEECAcIBQgCBQgDBwAHBwMABQUEAQMIBQYDAwgABAQIBwEA
CAUABAQEBgQEBgQDAAYBBAAFAwEDBwcEBQIAAAQFAAYAAgUDAQAECAUEBAICBQg
EBQEHAwYABgAEAgIAAAEEAQMAAAUBBQcEAgUHBQcCAQcIAwMCBQMEAgYBAAcFAg
IDAAMEBQIBCAEACAYABggEAwMABwYCAQgGAQUBBwQFAgUFBQgFAAMCAAMABwIEB
ggCBQMBBQABBgQHAgUCBAcCAwYHCAcGBgAEBgYAAQYIBwQBBAECCAICAQEIAQEI
BAICBQcHCAAFBAcBAQQDBgYCBggAAggEAgMEAwIDBwgFAwIBBAAIAQAFAwACCAg
CAwgFCAcBCAUBAAYFAAQGBwUGBwYABQQIBQYHAAUIBg==
DATA;

    public function testGetType(): void
    {
        $tkey = new TKEY();
        $this->assertEquals('TKEY', $tkey->getType());
    }

    public function testGetTypeCode(): void
    {
        $tkey = new TKEY();
        $this->assertEquals(249, $tkey->getTypeCode());
    }

    public function testToText(): void
    {
        $tkey = new TKEY();
        $tkey->setAlgorithm('alg-xx.iana.');
        $tkey->setInception(\DateTime::createFromFormat('YmdHis', '20191118000000'));
        $tkey->setExpiration(\DateTime::createFromFormat('YmdHis', '20251118000000'));
        $tkey->setMode(2);
        $tkey->setError(0);
        $tkey->setKeyData(base64_decode($this->dummyKeyData));
        $tkey->setOtherData(base64_decode($this->dummyOtherData));

        $expectation = 'alg-xx.iana. 1574035200 1763424000 2 0 ';
        $expectation .= str_replace([Tokens::CARRIAGE_RETURN, Tokens::LINE_FEED], '', $this->dummyKeyData).' ';
        $expectation .= str_replace([Tokens::CARRIAGE_RETURN, Tokens::LINE_FEED], '', $this->dummyOtherData);

        $this->assertEquals($expectation, $tkey->toText());
    }

    public function testWire(): void
    {
        $tkey = new TKEY();
        $tkey->setAlgorithm('alg-xx.iana.');
        $tkey->setInception(\DateTime::createFromFormat('YmdHis', '20191118000000'));
        $tkey->setExpiration(\DateTime::createFromFormat('YmdHis', '20251118000000'));
        $tkey->setMode(2);
        $tkey->setError(0);
        $tkey->setKeyData(base64_decode($this->dummyKeyData));
        $tkey->setOtherData(base64_decode($this->dummyOtherData));

        $wireFormat = $tkey->toWire();
        $rdLength = strlen($wireFormat);
        $wireFormat = 'abcdefg'.$wireFormat;
        $offset = 7;
        $fromWire = new TKEY();
        $fromWire->fromWire($wireFormat, $offset, $rdLength);
        $this->assertEquals($tkey, $fromWire);
        $this->assertEquals(7 + $rdLength, $offset);
    }

    public function testFromText(): void
    {
        $expectation = new TKEY();
        $expectation->setAlgorithm('alg-xx.iana.');
        $expectation->setInception(\DateTime::createFromFormat('YmdHis', '20191118000000'));
        $expectation->setExpiration(\DateTime::createFromFormat('YmdHis', '20251118000000'));
        $expectation->setMode(2);
        $expectation->setError(0);
        $expectation->setKeyData(base64_decode($this->dummyKeyData));
        $expectation->setOtherData(base64_decode($this->dummyOtherData));

        $text = 'alg-xx.iana. 1574035200 1763424000 2 0 ';
        $text .= str_replace([Tokens::CARRIAGE_RETURN, Tokens::LINE_FEED], '', $this->dummyKeyData).' ';
        $text .= str_replace([Tokens::CARRIAGE_RETURN, Tokens::LINE_FEED], '', $this->dummyOtherData);

        $fromText = new TKEY();
        $fromText->fromText($text);
        $this->assertEquals($expectation, $fromText);
    }

    public function testFactory(): void
    {
        $tkey = Factory::TKEY(
            'alg-xx.iana.',
            \DateTime::createFromFormat('YmdHis', '20191118000000'),
            \DateTime::createFromFormat('YmdHis', '20251118000000'),
            2,
            0,
            base64_decode($this->dummyKeyData),
            base64_decode($this->dummyOtherData)
        );

        $this->assertEquals('alg-xx.iana.', $tkey->getAlgorithm());
        $this->assertEquals('2019-11-18', $tkey->getInception()->format('Y-m-d'));
        $this->assertEquals('2025-11-18', $tkey->getExpiration()->format('Y-m-d'));
        $this->assertEquals(2, $tkey->getMode());
        $this->assertEquals(0, $tkey->getError());
        $this->assertEquals(base64_decode($this->dummyKeyData), $tkey->getKeyData());
        $this->assertEquals(base64_decode($this->dummyOtherData), $tkey->getOtherData());
    }
}
