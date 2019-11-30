# Reverse Records

The DNS Library can natively handle reverse IP records for both IPv4 and IPv6.

## IPv4 Example
```php
use Badcow\DNS\Rdata\Factory;
use Badcow\DNS\{Zone, ResourceRecord, AlignedBuilder, Classes, ZoneBuilder};
use Badcow\DNS\Ip\PTR;

$parent = PTR::reverseIpv4('158.133.7'); //Returns 7.133.158.in-addr.arpa.

$zone = new Zone($parent, 10800);

$resourceRecords = [
    new ResourceRecord('1', Factory::Ptr('gw-01.badcow.co.')),
    new ResourceRecord('2', Factory::Ptr('gw-02.badcow.co.')),
    new ResourceRecord('10', Factory::Ptr('badcow.co.')),
    new ResourceRecord('15', Factory::Ptr('mail.badcow.co.'), 3600),
    new ResourceRecord('51', Factory::Ptr('esw-01.badcow.co.')),
    new ResourceRecord('52', Factory::Ptr('esw-02.badcow.co.')),
];

$zone->fromArray($resourceRecords);

echo AlignedBuilder::build($zone);
```
### Output
```text
$ORIGIN 7.133.158.in-addr.arpa.
$TTL 10800

; PTR RECORDS
10      IN PTR badcow.co.
15 3600 IN PTR mail.badcow.co.
1       IN PTR gw-01.badcow.co.
2       IN PTR gw-02.badcow.co.
51      IN PTR esw-01.badcow.co.
52      IN PTR esw-02.badcow.co.
```

## IPv6 Example
```php
use Badcow\DNS\Rdata\Factory;
use Badcow\DNS\{Zone, ResourceRecord, AlignedBuilder, Classes, ZoneBuilder};
use Badcow\DNS\Ip\PTR;

//$parent = PTR::reverseIpv4('158.133.7'); //Returns 7.133.158.in-addr.arpa.
$parent = PTR::reverseIpv6('2001:acad:5889:0:0:0:0');

$zone = new Zone($parent, 10800);

$resourceRecords = [
    new ResourceRecord(PTR::reverseIpv6('1', false), Factory::Ptr('gw-01.badcow.co.')),
    new ResourceRecord(PTR::reverseIpv6('2', false), Factory::Ptr('gw-02.badcow.co.')),
    new ResourceRecord(PTR::reverseIpv6('bad', false), Factory::Ptr('badcow.co.')),
    new ResourceRecord(PTR::reverseIpv6('ff', false), Factory::Ptr('mail.badcow.co.'), 3600, Classes::INTERNET),
    new ResourceRecord(PTR::reverseIpv6('aa1', false), Factory::Ptr('esw-01.badcow.co.')),
    new ResourceRecord(PTR::reverseIpv6('aa2', false), Factory::Ptr('esw-02.badcow.co.')),
];

$zone->fromArray($resourceRecords);

echo AlignedBuilder::build($zone);
```
### Output
```text
$ORIGIN 0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.9.8.8.5.d.a.c.a.1.0.0.2.ip6.arpa.
$TTL 10800

; PTR RECORDS
1.0.0.0         PTR gw-01.badcow.co.
1.a.a.0         PTR esw-01.badcow.co.
2.0.0.0         PTR gw-02.badcow.co.
2.a.a.0         PTR esw-02.badcow.co.
d.a.b.0         PTR badcow.co.
f.f.0.0 3600 IN PTR mail.badcow.co.
```