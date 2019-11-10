Basic Usage
===========

```php
$file = file_get_contents('/path/to/example.com.txt');

$zone = Badcow\DNS\Parser\Parser::parse('example.com.', $file);
```

Simple as that.


## Example

### BIND Record
```text
$ORIGIN example.com.
$TTL 3600
@            IN SOA  (
                     example.com.       ; MNAME
                     post.example.com.  ; RNAME
                     2014110501         ; SERIAL
                     3600               ; REFRESH
                     14400              ; RETRY
                     604800             ; EXPIRE
                     3600               ; MINIMUM
                     )

 ; NS RECORDS
@               NS   ns1.nameserver.com.
@               NS   ns2.nameserver.com.

info            TXT "This is some additional \"information\""

 ; A RECORDS
sub.domain      A    192.168.1.42 ; This is a local ip.

 ; AAAA RECORDS
ipv6.domain    AAAA ::1 ; This is an IPv6 domain.

 ; MX RECORDS
@               MX   10 mail-gw1.example.net.
@               MX   20 mail-gw2.example.net.
@               MX   30 mail-gw3.example.net.

mail     IN     TXT  "THIS IS SOME TEXT; WITH A SEMICOLON"
```

### Processing the record
```php
<?php

require_once '/path/to/vendor/autoload.php';

$file = file_get_contents('/path/to/example.com.txt');
$zone = Badcow\DNS\Parser\Parser::parse('example.com.', $file);

$zone->getName(); //Returns example.com.
foreach ($zone->getResourceRecords() as $record) {
    $record->getName();
    $record->getClass();
    $record->getTtl();
    $record->getRdata()->toText();
}
```