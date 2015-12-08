<?php

/*
 * This file is part of Badcow DNS Library.
 *
 * (c) Samuel Williams <sam@badcow.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Badcow\DNS\Tests\Ip;

use Badcow\DNS\Ip\Toolbox;

class ToolboxTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     */
    public function testExpandIpv6()
    {
        $case_1 = '::1';
        $case_2 = '2001:db8::ff00:42:8329';
        $case_3 = '8007:ea:19';

        $exp_1 = '0000:0000:0000:0000:0000:0000:0000:0001';
        $exp_2 = '2001:0db8:0000:0000:0000:ff00:0042:8329';
        $exp_3 = '8007:00ea:0019';

        $this->assertEquals($exp_1, Toolbox::expandIpv6($case_1));
        $this->assertEquals($exp_2, Toolbox::expandIpv6($case_2));
        $this->assertEquals($exp_3, Toolbox::expandIpv6($case_3));
    }

    /**
     *
     */
    public function testReverseIpv4()
    {
        $case_1 = '192.168.1.213';
        $exp_1 = '213.1.168.192.in-addr.arpa.';

        $this->assertEquals($exp_1, Toolbox::reverseIpv4($case_1));
    }

    /**
     *
     */
    public function testReverseIpv6()
    {
        $case_1 = '2001:db8::567:89ab';
        $exp_1 = 'b.a.9.8.7.6.5.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.8.b.d.0.1.0.0.2.ip6.arpa.';

        $this->assertEquals($exp_1, Toolbox::reverseIpv6($case_1));
    }
}
