Parsing Comments
================
Badcow DNS Library Parser class can retain comments from BIND records once parsed as PHP objects. Before diving into the
syntax, it may prove useful to delineate the kinds of comments that are present in BIND records, because not all
comments are the same and some don't matter.

## Comment Types
BIND records can have three different comment types: inline, block headers, and multi-line.

### Inline comments
Inline comments appear at the end of record entry and is oftern the most useful, _exempli gratia_:
```TXT
acme.org IN 3200 MX 100 mail-gw10  ;This is an inline comment
```
### Record entry block headers
Block headers appear before a block of similarly grouped records. Depending on style, these may
separate resource record types or group records associated with a single machine. E.g.:
```TXT
;THESE RECORDS ARE FOR THE WEB SERVER
www IN A 250.150.50.33
ftp IN A 250.150.50.33
mysql IN A 250.150.50.33
```
### Multi-line record comments
These appear when RData is complex (such as the SOA record) to indicate what each
parameter means. E.g.
```TXT
@            IN SOA  (
                     ns.acme.com.       ; MNAME
                     noc.acme.com.      ; RNAME
                     2014110501         ; SERIAL
                     3600               ; REFRESH
                     14400              ; RETRY
                     604800             ; EXPIRE
                     3600               ; MINIMUM
                     )
```
### Combinations
These comment types may appear as a combination, for example:
```TXT
; LOC Records indicating the geographical location of a resource
canberra     IN LOC  (
                     35 18 27.000 S ; LATITUDE
                     149 7 27.840 E ; LONGITUDE
                     500.00m        ; ALTITUDE
                     20.12m         ; SIZE
                     200.30m        ; HORIZONTAL PRECISION
                     300.10m        ; VERTICAL PRECISION
                     ); This is Canberra
```

## Parsing

When parsing zones, you can choose which kinds of comments you want to include by using the `$commentOptions` parameter
on the `Badcow\DNS\Parser\Parser::parse()` method.
E.g.
```php
$exampleZone = file_get_contents('/path/to/example.com.db');
$zone = Parser::parse('example.com.', $exampleZone, Normaliser::COMMENTS_END_OF_RECORD_ENTRY);
```

The above will only include comments that are at the end of the resource record (i.e. no block headers or multi-line
comments).

The `$commentOptions` parameter accepts a bit-mask representing possible combinations of the above comment types.

| Name                                      | Integer Value | Comment Type               |
| ----------------------------------------- | :----------:  | :------------------------- |
| Normaliser::COMMENTS_NONE                 | 0             | No comments are parsed     |
| Normaliser::COMMENTS_END_OF_RECORD_ENTRY  | 1             | Inline comments            |
| Normaliser::COMMENTS_WITHIN_MULTILINE     | 2             | Multi-line record comments |
| Normaliser::COMMENTS_WITHOUT_RECORD_ENTRY | 4             | Block header comments      |
| Normaliser::COMMENTS_ALL                  | 7             | Include all comments       |

To make a combination, simply provide each comment type as a bitwise `OR`. E.g.
```php
//This excludes multi-line comments
$commentOptions = Normaliser::COMMENTS_WITHOUT_RECORD_ENTRY | Normaliser::COMMENTS_END_OF_RECORD_ENTRY; //Equals 5
$zone = Parser::parse('example.com.', $exampleZone, $commentOptions);
```

## Example

Take the following code...

```php
$bind = <<< 'BIND'
;This is the SOA
acme.com. 3600 IN SOA  (
                       ns.acme.com.       ; MNAME
                       noc.acme.com.      ; RNAME
                       2014110501         ; SERIAL
                       3600               ; REFRESH
                       14400              ; RETRY
                       604800             ; EXPIRE
                       3600               ; MINIMUM
                       ); This is an SOA
BIND;

$commentOptions = Normaliser::COMMENTS_WITHOUT_RECORD_ENTRY | Normaliser::COMMENTS_END_OF_RECORD_ENTRY | Normaliser::COMMENTS_WITHIN_MULTILINE;

$acmeZone = Parser::parse('acme.com.', $bind, $commentOptions);

foreach ($acmeZone as $resourceRecord) {
    echo $resourceRecord->getComment() . PHP_EOL;
}
```

Will return...
```TXT
This is the SOA
MNAME RNAME SERIAL REFRESH RETRY EXPIRE MINIMUM This is an SOA
```

Notice that "This is the SOA" is its own resource record object, it does not have a name, TTL, Class, or Rdata
associated with it. Also notice that "This is an SOA" is appended to the end of the multi-line comments, this is because
both comment types are considered to be apart of the same resource record. If you were to rebuild this zone, the
comments will likely be very different from the original. 
