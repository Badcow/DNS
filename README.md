Badcow DNS Zone Library
=======================

This library constructs DNS zone records based on [RFC1035](http://www.ietf.org/rfc/rfc1035.txt) and subsequent standards.

__Now with reverse record support!__

## Build Status
[![Build Status](https://travis-ci.org/Badcow/DNS.png)](https://travis-ci.org/Badcow/DNS) [![Code Coverage](https://scrutinizer-ci.com/g/Badcow/DNS/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Badcow/DNS/?branch=master) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Badcow/DNS/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Badcow/DNS/?branch=master)

## Example usage

```php
require_once __DIR__ . '/vendor/autoload.php';

use Badcow\DNS\Zone;
use Badcow\DNS\Rdata\Factory;
use Badcow\DNS\ResourceRecord;
use Badcow\DNS\AlignedBuilder;

$zone = new Zone('example.com.');
$zone->setDefaultTtl(3600);

$soa = new ResourceRecord;
$soa->setName('@');
$soa->setRdata(Factory::Soa(
    'example.com.',
    'post.example.com.',
    '2014110501',
    3600,
    14400,
    604800,
    3600
));

$ns1 = new ResourceRecord;
$ns1->setName('@');
$ns1->setRdata(Factory::Ns('ns1.nameserver.com.'));

$ns2 = new ResourceRecord;
$ns2->setName('@');
$ns2->setRdata(Factory::Ns('ns2.nameserver.com.'));

$a = new ResourceRecord;
$a->setName('sub.domain');
$a->setRdata(Factory::A('192.168.1.42'));
$a->setComment('This is a local ip.');

$a6 = new ResourceRecord;
$a6->setName('ipv6.domain');
$a6->setRdata(Factory::Aaaa('::1'));
$a6->setComment('This is an IPv6 domain.');

$mx1 = new ResourceRecord;
$mx1->setName('@');
$mx1->setRdata(Factory::Mx(10, 'mail-gw1.example.net.'));

$mx2 = new ResourceRecord;
$mx2->setName('@');
$mx2->setRdata(Factory::Mx(20, 'mail-gw2.example.net.'));

$mx3 = new ResourceRecord;
$mx3->setName('@');
$mx3->setRdata(Factory::Mx(30, 'mail-gw3.example.net.'));

$loc = new ResourceRecord;
$loc->setName('canberra');
$loc->setRdata(Factory::Loc(
    -35.3075,   //Lat
    149.1244,   //Lon
    500,        //Alt
    20.12,      //Size
    200.3,      //HP
    300.1       //VP
));
$loc->setComment('This is Canberra');

$zone->addResourceRecord($loc);
$zone->addResourceRecord($mx2);
$zone->addResourceRecord($soa);
$zone->addResourceRecord($ns1);
$zone->addResourceRecord($mx3);
$zone->addResourceRecord($a);
$zone->addResourceRecord($a6);
$zone->addResourceRecord($ns2);
$zone->addResourceRecord($mx1);

$zoneBuilder = new AlignedBuilder();

echo $zoneBuilder->build($zone);
```

### Output
```txt
$ORIGIN example.com.
$TTL 3600
@            IN SOA  (
                     example.com.      ; MNAME
                     post.example.com. ; RNAME
                     2014110501        ; SERIAL
                     3600              ; REFRESH
                     14400             ; RETRY
                     604800            ; EXPIRE
                     3600              ; MINIMUM
                     )

; NS RECORDS
@            IN NS   ns1.nameserver.com.
@            IN NS   ns2.nameserver.com.

; A RECORDS
sub.domain   IN A    192.168.1.42; This is a local ip.

; AAAA RECORDS
ipv6.domain  IN AAAA ::1; This is an IPv6 domain.

; MX RECORDS
@            IN MX   10 mail-gw1.example.net.
@            IN MX   20 mail-gw2.example.net.
@            IN MX   30 mail-gw3.example.net.

; LOC RECORDS
canberra     IN LOC  (
                     35 18 27.000 S ; LATITUDE
                     149 7 27.840 E ; LONGITUDE
                     500.00m        ; ALTITUDE
                     20.12m         ; SIZE
                     200.30m        ; HORIZONTAL PRECISION
                     300.10m        ; VERTICAL PRECISION
                     ); This is Canberra
```

The above is an example of the `AlignedBuilder` which creates records that are much more aesthetically pleasing. You can also use the flat ZoneBuilder, whose output looks like below:

```php
...
$zoneBuilder = new ZoneBuilder();

echo $zoneBuilder->build($zone);
```
```txt
$ORIGIN example.com.
$TTL 3600
canberra  IN LOC 35 18 27.000 S 149 7 27.840 E 500.00m 20.12m 200.30m 300.10m; This is Canberra
@  IN MX 20 mail-gw2.example.net.
@  IN SOA example.com. post.example.com. 2014110501 3600 14400 604800 3600
@  IN NS ns1.nameserver.com.
@  IN MX 30 mail-gw3.example.net.
sub.domain  IN A 192.168.1.42; This is a local ip.
ipv6.domain  IN AAAA ::1; This is an IPv6 domain.
@  IN NS ns2.nameserver.com.
@  IN MX 10 mail-gw1.example.net.
```

## Reverse records now supported

```php
use Badcow\DNS\ZoneBuilder;
use Badcow\DNS\Zone;
use Badcow\DNS\Ip\Toolbox;
use Badcow\DNS\Rdata\Factory;
use Badcow\DNS\ResourceRecord;
use Badcow\DNS\Classes;

$origin = Toolbox::reverseIpv4('192.168.8');

$soa = new ResourceRecord('@', Factory::Soa(
    'example.com.',
    'post.example.com.',
    2015010101,
    3600,
    14400,
    604800,
    3600
), null, Classes::INTERNET);

$ns = new ResourceRecord('@', Factory::Ns('ns.example.com.'), null, Classes::INTERNET);
$foo = new ResourceRecord('1', Factory::Ptr('foo.example.com.'), null, Classes::INTERNET);
$bar = new ResourceRecord('84', Factory::Ptr('bar.example.com.'), null, Classes::INTERNET);
$foobar = new ResourceRecord('128', Factory::Ptr('foobar.example.com.'), null, Classes::INTERNET);

$zone = new Zone($origin, 14400, array(
    $soa,
    $ns,
    $foo,
    $bar,
    $foobar
));

$builder = new ZoneBuilder();
echo $builder->build($zone);
```

### Output

```txt
$ORIGIN 8.168.192.in-addr.arpa.
$TTL 14400
@ IN SOA example.com. post.example.com. 2015010101 3600 14400 604800 3600
@ IN NS ns.example.com.
1 IN PTR foo.example.com.
84 IN PTR bar.example.com.
128 IN PTR foobar.example.com.
```

## Reverse IPv6 records

```php
use Badcow\DNS\AlignedBuilder;
use Badcow\DNS\Zone;
use Badcow\DNS\Ip\Toolbox;
use Badcow\DNS\Rdata\Factory;
use Badcow\DNS\ResourceRecord;
use Badcow\DNS\Classes;

$origin = Toolbox::reverseIpv6('2001:f83:21:5004:a56:786:1');

$soa = new ResourceRecord('@', Factory::Soa(
    'example.com.',
    'post.example.com.',
    2015010101,
    3600,
    14400,
    604800,
    3600
), null, Classes::INTERNET);

$ns1 = new ResourceRecord('@', Factory::Ns('ns1.example.com.'), null, Classes::INTERNET);
$ns2 = new ResourceRecord('@', Factory::Ns('ns2.example.com.'), null, Classes::INTERNET);

$foo8 = new ResourceRecord('8', Factory::Ptr('foo8.example.com.'), null, Classes::INTERNET);
$foo9 = new ResourceRecord('9', Factory::Ptr('foo9.example.com.'), null, Classes::INTERNET);
$fooa = new ResourceRecord('a', Factory::Ptr('fooa.example.com.'), null, Classes::INTERNET);
$foob = new ResourceRecord('b', Factory::Ptr('foob.example.com.'), null, Classes::INTERNET);
$fooc = new ResourceRecord('c', Factory::Ptr('fooc.example.com.'), null, Classes::INTERNET);

$zone = new Zone($origin, 14400, array(
    $soa,
    $ns1,
    $ns2,
    $foo8,
    $foo9,
    $fooa,
    $foob,
    $fooc,
));

$builder = new \Badcow\DNS\ZoneBuilder();
echo $builder->build($zone);
```

### Output

```txt
$ORIGIN 1.0.0.0.6.8.7.0.6.5.a.0.4.0.0.5.1.2.0.0.3.8.f.0.1.0.0.2.ip6.arpa.
$TTL 14400
@ IN SOA example.com. post.example.com. 2015010101 3600 14400 604800 3600
@ IN NS ns1.example.com.
@ IN NS ns2.example.com.
8 IN PTR foo8.example.com.
9 IN PTR foo9.example.com.
a IN PTR fooa.example.com.
b IN PTR foob.example.com.
c IN PTR fooc.example.com.
```

## Running the tests

Simply use phpunit to run the tests. You can run additional tests if you have BIND installed. Add the environment variable to `phpunit.xml`:
    <env name="CHECKZONE_PATH" value="/path/to/named-checkzone"/>

Or add it at run-time:

    CHECKZONE_PATH="/path/to/named-checkzone" phpunit .
