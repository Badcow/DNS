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
use Badcow\DNS\Rdata\HIP;
use PHPUnit\Framework\TestCase;

class HipTest extends TestCase
{
    private $publicKey;
    private $hit;
    private $rvs = [
        'rvs0.example.com.',
        'rvs1.example.com.',
        'rvs2.example.com.',
    ];

    public function setUp(): void
    {
        $this->publicKey = base64_decode(
            'AwEAAbdxyhNuSutc5EMzxTs9LBPCIkOFH8cI
            vM4p9+LrV4e19WzK00+CI6zBCQTdtWsuxKbWIy87UOoJTwkUs7lBu+Upr1gsNrut79ry
            ra+bSRGQb1slImA8YVJyuIDsj7kwzG7jnERNqnWxZ48AWkskmdHaVDP4BcelrTI3rMXd
            XF5D'
        );

        $this->hit = hex2bin('200100107B1A74DF365639CC39F1D578');
    }

    public function testGetType(): void
    {
        $hip = new HIP();
        $this->assertEquals('HIP', $hip->getType());
    }

    public function testGetTypeCode(): void
    {
        $hip = new HIP();
        $this->assertEquals(55, $hip->getTypeCode());
    }

    public function testToText(): void
    {
        $hip = new HIP();
        $hip->setPublicKeyAlgorithm(2);
        $hip->setPublicKey($this->publicKey);
        $hip->setHostIdentityTag($this->hit);
        array_map([$hip, 'addRendezvousServer'], $this->rvs);
        $expectation = '2 200100107b1a74df365639cc39f1d578 AwEAAbdxyhNuSutc5EMzxTs9LBPCIkOFH8cIvM4p9+LrV4e19WzK00+CI6zBCQTdtWsuxKbWIy87UOoJTwkUs7lBu+Upr1gsNrut79ryra+bSRGQb1slImA8YVJyuIDsj7kwzG7jnERNqnWxZ48AWkskmdHaVDP4BcelrTI3rMXdXF5D rvs0.example.com. rvs1.example.com. rvs2.example.com.';

        $this->assertEquals($expectation, $hip->toText());
    }

    public function testWire(): void
    {
        $hip = new HIP();
        $hip->setPublicKeyAlgorithm(2);
        $hip->setPublicKey($this->publicKey);
        $hip->setHostIdentityTag($this->hit);
        array_map([$hip, 'addRendezvousServer'], $this->rvs);
        $wireFormat = $hip->toWire();

        $fromWire = new HIP();
        $fromWire->fromWire($wireFormat);

        $this->assertEquals($hip, $fromWire);
    }

    public function testFromText(): void
    {
        $expectation = new HIP();
        $expectation->setPublicKeyAlgorithm(2);
        $expectation->setPublicKey($this->publicKey);
        $expectation->setHostIdentityTag($this->hit);
        array_map([$expectation, 'addRendezvousServer'], $this->rvs);
        $text = '2 200100107b1a74df365639cc39f1d578 AwEAAbdxyhNuSutc5EMzxTs9LBPCIkOFH8cIvM4p9+LrV4e19WzK00+CI6zBCQTdtWsuxKbWIy87UOoJTwkUs7lBu+Upr1gsNrut79ryra+bSRGQb1slImA8YVJyuIDsj7kwzG7jnERNqnWxZ48AWkskmdHaVDP4BcelrTI3rMXdXF5D rvs0.example.com. rvs1.example.com. rvs2.example.com.';

        $fromText = new HIP();
        $fromText->fromText($text);
        $this->assertEquals($expectation, $fromText);
    }

    public function testFactory(): void
    {
        $hip = Factory::HIP(2, $this->hit, $this->publicKey, $this->rvs);

        $this->assertEquals(2, $hip->getPublicKeyAlgorithm());
        $this->assertEquals($this->publicKey, $hip->getPublicKey());
        $this->assertEquals($this->hit, $hip->getHostIdentityTag());
        $this->assertEquals($this->rvs, $hip->getRendezvousServers());
    }
}
