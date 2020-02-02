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

use Badcow\DNS\Rdata\DecodeException;
use Badcow\DNS\Rdata\Factory;
use Badcow\DNS\Rdata\IPSECKEY;
use PHPUnit\Framework\TestCase;

class IpseckeyTest extends TestCase
{
    public function getDataProvider(): array
    {
        return [
            // Text, Precedence, GatewayType, Algorithm, Gateway, PublicKey
            ['10 1 2 192.0.2.38 AQNRU3mG7TVTO2BkR47usntb102uFJtugbo6BSGvgqt4AQ==', 10, 1, 2, '192.0.2.38', 'AQNRU3mG7TVTO2BkR47usntb102uFJtugbo6BSGvgqt4AQ=='],
            ['10 0 2 . AQNRU3mG7TVTO2BkR47usntb102uFJtugbo6BSGvgqt4AQ==', 10, 0, 2, null, 'AQNRU3mG7TVTO2BkR47usntb102uFJtugbo6BSGvgqt4AQ=='],
            ['10 1 2 192.0.2.3 AQNRU3mG7TVTO2BkR47usntb102uFJtugbo6BSGvgqt4AQ==', 10, 1, 2, '192.0.2.3', 'AQNRU3mG7TVTO2BkR47usntb102uFJtugbo6BSGvgqt4AQ=='],
            ['10 3 2 mygateway.example.com. AQNRU3mG7TVTO2BkR47usntb102uFJtugbo6BSGvgqt4AQ==', 10, 3, 2, 'mygateway.example.com.', 'AQNRU3mG7TVTO2BkR47usntb102uFJtugbo6BSGvgqt4AQ=='],
            ['10 3 0 mygateway.example.com.', 10, 3, 0, 'mygateway.example.com.', null],
            ['10 2 2 2001:db8:0:8002::2000:1 AQNRU3mG7TVTO2BkR47usntb102uFJtugbo6BSGvgqt4AQ==', 10, 2, 2, '2001:db8:0:8002::2000:1', 'AQNRU3mG7TVTO2BkR47usntb102uFJtugbo6BSGvgqt4AQ=='],
        ];
    }

    public function testGetType(): void
    {
        $ipseckey = new IPSECKEY();
        $this->assertEquals('IPSECKEY', $ipseckey->getType());
    }

    public function testGetTypeCode(): void
    {
        $ipseckey = new IPSECKEY();
        $this->assertEquals(45, $ipseckey->getTypeCode());
    }

    /**
     * @dataProvider getDataProvider
     *
     * @param string      $text
     * @param int         $precedence
     * @param int         $gatewayType
     * @param int         $algorithm
     * @param string|null $gateway
     * @param string|null $publicKey
     */
    public function testToText(string $text, int $precedence, int $gatewayType, int $algorithm, ?string $gateway, ?string $publicKey): void
    {
        $ipseckey = new IPSECKEY();
        $ipseckey->setPrecedence($precedence);
        $ipseckey->setGateway($gateway);
        $ipseckey->setPublicKey($algorithm, $publicKey);

        $this->assertEquals($text, $ipseckey->toText());
    }

    /**
     * @dataProvider getDataProvider
     *
     * @param string      $text
     * @param int         $precedence
     * @param int         $gatewayType
     * @param int         $algorithm
     * @param string|null $gateway
     * @param string|null $publicKey
     *
     * @throws DecodeException
     */
    public function testToFromWire(string $text, int $precedence, int $gatewayType, int $algorithm, ?string $gateway, ?string $publicKey): void
    {
        $ipseckey = new IPSECKEY();
        $ipseckey->setPrecedence($precedence);
        $ipseckey->setGateway($gateway);
        $ipseckey->setPublicKey($algorithm, $publicKey);

        $wireFormat = $ipseckey->toWire();
        $rdLength = strlen($wireFormat);
        $wireFormat = 'abc'.$wireFormat;
        $offset = 3;
        $this->assertEquals($ipseckey, IPSECKEY::fromWire($wireFormat, $offset, $rdLength));
        $this->assertEquals(3 + $rdLength, $offset);
    }

    /**
     * @dataProvider getDataProvider
     *
     * @param string      $text
     * @param int         $precedence
     * @param int         $gatewayType
     * @param int         $algorithm
     * @param string|null $gateway
     * @param string|null $publicKey
     */
    public function testFromText(string $text, int $precedence, int $gatewayType, int $algorithm, ?string $gateway, ?string $publicKey): void
    {
        $ipseckey = IPSECKEY::fromText($text);

        $this->assertEquals($precedence, $ipseckey->getPrecedence());
        $this->assertEquals($gatewayType, $ipseckey->getGatewayType());
        $this->assertEquals($algorithm, $ipseckey->getAlgorithm());
        $this->assertEquals($gateway, $ipseckey->getGateway());
        $this->assertEquals($publicKey, $ipseckey->getPublicKey());
    }

    /**
     * @dataProvider getDataProvider
     *
     * @param string      $text
     * @param int         $precedence
     * @param int         $gatewayType
     * @param int         $algorithm
     * @param string|null $gateway
     * @param string|null $publicKey
     */
    public function testFactory(string $text, int $precedence, int $gatewayType, int $algorithm, ?string $gateway, ?string $publicKey): void
    {
        $ipseckey = Factory::IPSECKEY($precedence, $gateway, $algorithm, $publicKey);

        $this->assertEquals($text, $ipseckey->toText());
        $this->assertEquals($precedence, $ipseckey->getPrecedence());
        $this->assertEquals($gatewayType, $ipseckey->getGatewayType());
        $this->assertEquals($algorithm, $ipseckey->getAlgorithm());
        $this->assertEquals($gateway, $ipseckey->getGateway());
        $this->assertEquals($publicKey, $ipseckey->getPublicKey());
    }
}
