Badcow DNS Zone Library
=======================
The aim of this project is to create abstract object representations of DNS records in PHP. The project consists of various
classes representing DNS objects (such as `Zone`, `ResourceRecord`, and various `RData` types), a parser to convert BIND
style text files to the PHP objects, and builders to create aesthetically pleasing BIND records.

## Build Status
[![Build Status](https://travis-ci.org/Badcow/DNS.png)](https://travis-ci.org/Badcow/DNS) [![Code Coverage](https://scrutinizer-ci.com/g/Badcow/DNS/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Badcow/DNS/?branch=master) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Badcow/DNS/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Badcow/DNS/?branch=master)

## Contents
1. [Example usage](#example-usage)
2. [Example Output](#output)
3. [Supported Types](#supported-types)
4. [Parsing BIND Records](#parsing-bind-records)

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

echo AlignedBuilder::build($zone);
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

The above is an example of the `AlignedBuilder` which creates records that are much more aesthetically pleasing. You can
also use the flat `ZoneBuilder`, the output of which is below:

```php
...
echo ZoneBuilder::build($zone);
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

## Supported Types
* `A`
* `AAAA`
* `APL`
* `CNAME`
* `CAA`
* `DNAME`
* `HINFO`
* `LOC`
* `MX`
* `NS`
* `PTR`
* `SOA`
* `SRV`
* `TXT`
* DNSSEC specific types:
  * `DNSKEY`
  * `DS`
  * `NSEC`
  * `RRSIG`

## Parsing BIND Records

BIND Records can be parsed into PHP objects using `Badcow\DNS\Parser\Parser`

```php
$file = file_get_contents('/path/to/example.com.txt');
$zone = Badcow\DNS\Parser\Parser::parse('example.com.', $file); //Badcow Zone Object
```

Simple as that.

More examples can be found in the [The Docs](docs/Parser)
