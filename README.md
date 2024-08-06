Badcow DNS Library
==================
The aim of this project is to create abstract object representations of DNS records in PHP. The project consists of various
classes representing DNS objects (such as `Zone`, `ResourceRecord`, and various `RData` types), a parser to convert BIND
style text files to the PHP objects, and builders to create aesthetically pleasing BIND records.

The library can parse and encode DNS messages enabling developers to create DNS client/server platforms in pure PHP.

## Build Status
[![Build Status: PHP 8](https://github.com/Badcow/DNS/workflows/PHP%208/badge.svg)](https://github.com/Badcow/DNS/actions?query=workflow%3A%22PHP+8%22)

## Contents
1. [Example usage](#example-usage)
2. [Example Output](#output)
3. [Supported Types](#supported-types)
4. [Parsing BIND Records](#parsing-bind-records)

## Example usage

```php
require_once '/path/to/vendor/autoload.php';

use Badcow\DNS\Classes;
use Badcow\DNS\Zone;
use Badcow\DNS\Rdata\Factory;
use Badcow\DNS\ResourceRecord;
use Badcow\DNS\AlignedBuilder;

$zone = new Zone('example.com.');
$zone->setDefaultTtl(3600);

$soa = new ResourceRecord;
$soa->setName('@');
$soa->setClass(Classes::INTERNET);
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
$ns1->setClass(Classes::INTERNET);
$ns1->setRdata(Factory::Ns('ns1.nameserver.com.'));

$ns2 = new ResourceRecord;
$ns2->setName('@');
$ns2->setClass(Classes::INTERNET);
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

$zone->addResourceRecord($soa);
$zone->addResourceRecord($mx2);
$zone->addResourceRecord($ns1);
$zone->addResourceRecord($mx3);
$zone->addResourceRecord($a);
$zone->addResourceRecord($a6);
$zone->addResourceRecord($ns2);
$zone->addResourceRecord($mx1);

$builder = new AlignedBuilder();
echo $builder->build($zone);
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
sub.domain      A    192.168.1.42; This is a local ip.

; AAAA RECORDS
ipv6.domain     AAAA ::1; This is an IPv6 domain.

; MX RECORDS
@               MX   10 mail-gw1.example.net.
@               MX   20 mail-gw2.example.net.
@               MX   30 mail-gw3.example.net.
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
@ IN SOA example.com. post.example.com. 2014110501 3600 14400 604800 3600
@ MX 20 mail-gw2.example.net.
@ IN NS ns1.nameserver.com.
@ MX 30 mail-gw3.example.net.
sub.domain A 192.168.1.42; This is a local ip.
ipv6.domain AAAA ::1; This is an IPv6 domain.
@ IN NS ns2.nameserver.com.
@ MX 10 mail-gw1.example.net.
```

## Supported Types
All ubiquitous DNS types are supported. For full details on supported types see [the Documentation](docs/Supported-Types.md).

## Parsing BIND Records

BIND Records can be parsed into PHP objects using `Badcow\DNS\Parser\Parser`

```php
$file = file_get_contents('/path/to/example.com.txt');
$zone = Badcow\DNS\Parser\Parser::parse('example.com.', $file); //Badcow Zone Object
```

Simple as that.

More examples can be found in the [The Docs](docs/Parser)
