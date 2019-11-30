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

use Badcow\DNS\Classes;
use Badcow\DNS\Parser\ParseException;
use Badcow\DNS\Parser\Parser;
use Badcow\DNS\Rdata\TXT;
use PHPUnit\Framework\TestCase;

class CustomHandlerTest extends TestCase
{
    public function spfHandler(\ArrayIterator $iterator): TXT
    {
        $string = '';
        while ($iterator->valid()) {
            $string .= $iterator->current().' ';
            $iterator->next();
        }
        $string = trim($string, ' "'); //Remove whitespace and quotes

        $spf = new TXT();
        $spf->setText($string);

        return $spf;
    }

    /**
     * @throws ParseException
     */
    public function testCustomHandler(): void
    {
        $customHandlers = ['SPF' => [$this, 'spfHandler']];

        $record = 'example.com. 7200 IN SPF "v=spf1 a mx ip4:69.64.153.131 include:_spf.google.com ~all"';
        $parser = new Parser($customHandlers);
        $zone = $parser->makeZone('example.com.', $record);
        $rr = $zone->getResourceRecords()[0];

        $this->assertEquals('TXT', $rr->getType());
        $this->assertEquals('example.com.', $rr->getName());
        $this->assertEquals(7200, $rr->getTtl());
        $this->assertEquals(Classes::INTERNET, $rr->getClass());
        $this->assertNotNull($rr->getRdata());
        $this->assertEquals('v=spf1 a mx ip4:69.64.153.131 include:_spf.google.com ~all', $rr->getRdata()->getText());
    }
}
