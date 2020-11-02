$INCLUDE Directive
==================
[RFC-1035](https://www.ietf.org/rfc/rfc1035.txt) section 5.1 permits the `$INCLUDE` control entry for importing external
files:
```
$INCLUDE <file-name> [<domain-name>] [<comment>]
```
In the specification, the `file-name` can be absolute or relative; the `domain-name` is optional and, if set, specifies
the `$ORIGIN` to be used for the imported sub-zone. _Exempli gratia:_
```
$INCLUDE headquarters.example.com.db headquarters.example.com. ;This imports the headquarters subdomain.
```
The Badcow DNS Parser will import these files if you have specified a class implementing the `Badcow\DNS\Parser\ZoneFileFetcherInterface`.
This class is then passed to the parser as the second argument on its constructor:
```
\Badcow\DNS\Parser\Parser::__construct(array $rdataHandlers, ZoneFileFetcherInterface $fetcher);
```
The interface has only one method to be implemented, `ZoneFileFetcherInterface::fetch(string $path)`. This is called within
the parser to fetch included files. This has been implemented to ensure that arbitrary files cannot be included by a zone.
Additionally, it may be the case that the zone files are not kept on a local disk, so a zoneFileFetcher can be designed
to grab files from any location (an SMB server, for example).

## Example
The following zone file includes some subdomains at the end:
```
;/home/dns/zones/testdomain.geek.db
$ORIGIN testdomain.geek.
$TTL 7200
@ IN SOA testdomain.geek. post.testdomain.geek. 2014110501 3600 14400 604800 3600
@ IN NS ns1.nameserver.com.
@ IN NS ns2.nameserver.com.

$INCLUDE email-domains.txt mail.testdomain.geek.
```

```php
<?php
use \Badcow\DNS\Parser\ZoneFileFetcherInterface;
use \Badcow\DNS\Parser\Parser;

//Define a ZoneFileFetcher
$zoneFetcher = new class() implements ZoneFileFetcherInterface {
    public function fetch(string $path): string
    {
        return file_get_contents('/home/dns/zones/mail-domains/'.$path);
    }
};

$parser = new Parser([], $zoneFetcher);
$zoneFile = file_get_contents('/home/dns/zones/testdomain.geek.db');

$zone = $parser->makeZone('testdomain.geek.', $zoneFile);
```