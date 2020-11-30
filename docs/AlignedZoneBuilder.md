AlignedZoneBuilder
==================
The `Badcow\DNS\AlignedZoneBuilder` class takes a `Badcow\DNS\Zone` and creates aesthetically pleasing BIND style zone
record.

## Example
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

## Customisations
### Resource Record Order
You can change the order in which the Resource Records are rendered, e.g.
```php
$alignedBuilder = new \Badcow\DNS\AlignedBuilder();
$myNewOrder = ['SOA', 'A', 'MX', 'AAAA', 'NS'];
$alignedBuilder->setOrder($myNewOrder);
echo $alignedBuilder->build($zone);
```
#### Output
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

; A RECORDS
sub.domain      A    192.168.1.42; This is a local ip.

; MX RECORDS
@               MX   10 mail-gw1.example.net.
@               MX   20 mail-gw2.example.net.
@               MX   30 mail-gw3.example.net.

; AAAA RECORDS
ipv6.domain     AAAA ::1; This is an IPv6 domain.

; NS RECORDS
@            IN NS   ns1.nameserver.com.
@            IN NS   ns2.nameserver.com.
```

### Adding special handlers

It may be the case that you want to define (or change) the way that some Rdata is formatted; you can define custom Rdata
formatters in the AlignedBuilder. The parameters that are exposed to the callable are:
 * `\Badcow\DNS\Rdata\RdataInterface $rdata` This is the Rdata that needs special handling.
 * `int $padding` the amount of spaces before the start of the Rdata column

Below is an example where `TXT` rdata is split over multiple lines:
```php
function specialTxtFormatter(Badcow\DNS\Rdata\TXT $rdata, int $padding): string
{
    //If the text length is less than or equal to 50 characters, just return it unaltered.
    if (strlen($rdata->getText()) <= 50) {
        return sprintf('"%s"', addcslashes($rdata->getText(), '"\\'));
    }

    $returnVal = "(\n";
    $chunks = str_split($rdata->getText(), 50);
    foreach ($chunks as $chunk) {
        $returnVal .= str_repeat(' ', $padding).
            sprintf('"%s"', addcslashes($chunk, '"\\')).
            "\n";
    }
    $returnVal .= str_repeat(' ', $padding) . ")";

    return $returnVal;
}

$zone = new Badcow\DNS\Zone('example.com.');
$zone->setDefaultTtl(3600);

$txt = new Badcow\DNS\ResourceRecord;
$txt->setName('txt.example.com.');
$txt->setClass('IN');
$txt->setRdata(Badcow\DNS\Rdata\Factory::Txt(
    'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque ac suscipit risus. Curabitur ac urna et quam'.
    'porttitor bibendum ut ac ipsum. Duis congue diam sed velit interdum ornare. Nullam dolor quam, aliquam sit amet'.
    'lacinia vel, rutrum et lacus. Aenean condimentum, massa a consectetur feugiat, massa augue accumsan tellus, ac'.
    'fringilla turpis velit a velit. Nunc ut tincidunt nisi. Ut pretium laoreet nisi, quis commodo lectus porta'.
    'vulputate. Vestibulum ullamcorper sed sapien ut venenatis. Morbi ut nulla eget dolor mattis dictum. Suspendisse'.
    'ut rutrum quam. Praesent id mi id justo maximus tristique.'
));

$zone->addResourceRecord($txt);

$alignedBuilder = new Badcow\DNS\AlignedBuilder();
$alignedBuilder->addRdataFormatter('TXT', 'specialTxtFormatter');

echo $alignedBuilder->build($zone);
```
####Output
```
$ORIGIN example.com.
$TTL 3600

; TXT RECORDS
txt.example.com.  IN TXT (
                         "Lorem ipsum dolor sit amet, consectetur adipiscing"
                         " elit. Quisque ac suscipit risus. Curabitur ac urn"
                         "a et quamporttitor bibendum ut ac ipsum. Duis cong"
                         "ue diam sed velit interdum ornare. Nullam dolor qu"
                         "am, aliquam sit ametlacinia vel, rutrum et lacus. "
                         "Aenean condimentum, massa a consectetur feugiat, m"
                         "assa augue accumsan tellus, acfringilla turpis vel"
                         "it a velit. Nunc ut tincidunt nisi. Ut pretium lao"
                         "reet nisi, quis commodo lectus portavulputate. Ves"
                         "tibulum ullamcorper sed sapien ut venenatis. Morbi"
                         " ut nulla eget dolor mattis dictum. Suspendisseut "
                         "rutrum quam. Praesent id mi id justo maximus trist"
                         "ique."
                         )
```