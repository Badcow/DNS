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

namespace Badcow\DNS\Tests\Github;

use Badcow\DNS\Parser\ParseException;
use Badcow\DNS\Parser\Parser;
use Badcow\DNS\Rdata\A;
use Badcow\DNS\Rdata\Algorithms;
use Badcow\DNS\Rdata\RRSIG;
use PHPUnit\Framework\TestCase;

class Issue60Test extends TestCase
{
    private $zone = <<<DNS
poose.eu.		3600	IN SOA	ns.zone.eu. hostmaster.zone.eu. (
					2019122947 ; serial
					10800      ; refresh (3 hours)
					3600       ; retry (1 hour)
					2419200    ; expire (4 weeks)
					3600       ; minimum (1 hour)
					)
			3600	RRSIG	SOA 14 2 3600 (
					20200112073532 20191229133101 55179 poose.eu.
					utZQyR9wDynN8JE8DCVwYxix9eXpx45c4kDR
					ZqwzZnNVbz8UQv8Mt/AAqyqiKq4kHvGeczSp
					jj2fR8UIra3/eEowfeJzzPPuKmUsYO0WLuFy
					Ef8PJut23NefeEWo+1F7 )
			3600	NS	ns.zone.eu.
			3600	NS	ns2.zone.ee.
			3600	NS	ns3.zonedata.net.
			3600	RRSIG	NS 14 2 3600 (
					20200112073532 20191229133101 55179 poose.eu.
					bpGJmk6mA55QvkYejr33387hvWkg9z4pyhw4
					OLcw4NxTSVe8yna81Ey6jpbkkTTJxPBjo5fI
					kVNX3+12qG4fw4kswDzmzRvlTqXmujoLMbxx
					aAGW2YMATNJXM1d7PG77 )
			3600	A	217.146.69.44
			3600	RRSIG	A 14 2 3600 (
					20200112073532 20191229133101 55179 poose.eu.
					bDts/7a5qbal6s3ZYzS5puPSjEfys5yI6R/k
					prBBRDEfVcT6YwPaDT3VkVjKXdvpKX2/Dwpi
					jNAWkjpfsewCLmeImx3RgkzfuxfipRKtBUgu
					iPTBhkj/ft2halJziVXl )
DNS;

    /**
     * @throws ParseException
     */
    public function testIssue(): void
    {
        $zone = Parser::parse('poose.eu.', $this->zone);

        $this->assertCount(8, $zone);

        /** @var RRSIG $rrsig */
        $rrsig = $zone[7]->getRdata();

        $expectedExpiration = new \DateTime('2020-01-12 07:35:32');
        $expectedInception = new \DateTime('2019-12-29 13:31:01');
        $expectedSignature = 'bDts/7a5qbal6s3ZYzS5puPSjEfys5yI6R/kprBBRDEfVcT6YwPaDT3VkVjKXdvpKX2/DwpijNAWkjpfsewCLmeImx3RgkzfuxfipRKtBUguiPTBhkj/ft2halJziVXl';

        $this->assertEquals(A::TYPE, $rrsig->getTypeCovered());
        $this->assertEquals(Algorithms::ECDSAP384SHA384, $rrsig->getAlgorithm());
        $this->assertEquals(2, $rrsig->getLabels());
        $this->assertEquals(3600, $rrsig->getOriginalTtl());
        $this->assertEquals($expectedExpiration, $rrsig->getSignatureExpiration());
        $this->assertEquals($expectedInception, $rrsig->getSignatureInception());
        $this->assertEquals(55179, $rrsig->getKeyTag());
        $this->assertEquals('poose.eu.', $rrsig->getSignersName());
        $this->assertEquals($expectedSignature, $rrsig->getSignature());
    }
}
