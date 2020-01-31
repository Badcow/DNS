ResourceRecord
==============
There are five (5) components of a Resource Record:
- **Name** - an owner name, i.e., the name of the node to which this resource record pertains.
- **Type** - The resource type. E.g. A, MX, CNAME, SOA, etcetera.
- **Class** - The resource record class. This MUST be the same for the entire zone. The class is nearly always _IN (INTERNET)_.
These classes map to class identifiers that have been [defined by IANA](https://www.iana.org/assignments/dns-parameters/dns-parameters.xhtml#dns-parameters-2).
- **Time-to-live (TTL)** - a 32 bit signed integer that specifies the time interval that the resource record may be
cached before the source of the information should again be consulted.
- **RData** - A variable string that describes a resource.
- **Comment** - A string giving context and additional information about the resource record. This does not get processed
by the DNS server.

## ResourceRecord Properties
These five components have been mapped to the `Badcow\DNS\ResourceRecord` class. The `ResourceRecord` class has the following properties:
- `$name (string)` - This can be a fully-qualified domain name, and unqualified domain name, `@` (meaning it takes the
name of the parent zone), or `null`.
- `$classId (int)` - This is an 8-bit integer that refers to a class as defined by IANA. The default is `1` (INTERNET).
- `$ttl (int)` - This is a 32-bit signed integer. This can be `null` (default).
- `$rdata (\Badcow\DNS\Rdata\RdataInterface)` - Any of the over forty RDATA classes that have been defined in this library.
- `$comment (string)` - A string of any length that gives additional details about the resource record. This is intended
for human use only.

The Rdata Type property is not an explicit property, it is an inferred property from the Rdata object.

### Property Accessors
The name, class ID, TTL, Rdata, and comment properties can be accessed using the corresponding "getters".
- `ResourceRecord::getName(): string`
- `ResourceRecord::getClassId(): int`
- `ResourceRecord::getClass(): string` - Class name associated with the class ID (e.g. `IN`, `CH`, `HS`, `CLASS42`, etc.)
- `ResourceRecord::getTtl(): int`
- `ResourceRecord::getRdata(): \Badcow\DNS\Rdata\RdataInterface`
- `ResourceRecord::getType(): string` - The Rdata type as a string.
- `ResourceRecord::getComment(): string`

### Property Assigners
All properties can be assigned with corresponding "setters".
- `ResourceRecord::setName(string $name)`
- `ResourceRecord::setClassId(int $classId)`
- `ResourceRecord::setClass(string $class)` - This will look-up and set the corresponding class ID. If no such
class is defined, the method will throw an `\InvalidArgument` exception.
- `ResourceRecord::setTtl(int $ttl)`
- `ResourceRecord::setRdata(\Badcow\DNS\Rdata\RdataInterface $rdata)`
- `ResourceRecord::setComment(string $comment)`

**Note:** you cannot set the rdata type, this is inferred from the Rdata object.

## Factory Method
`ResourceRecord::create()` is a static factory method for creating a populated version of itself.

`ResourceRecord::create(string $name, RdataInterface $rdata, int $ttl = null, string $class = Classes::INTERNET, string $comment = null): ResourceRecord`

### Example
```php
$a = new \Badcow\DNS\Rdata\A();
$resourceRecord = \Badcow\DNS\ResourceRecord::create('example.com.', $a, 3600, 'IN', 'This is an A record.');
```

**Note:** Only the `$name` and `$rdata` parameters are required.

## To and From Wire-formatted Binary Strings.
`ResourceRecord::toWire()` will return a string that is "wire ready" - that is, a form that can be transmitted to DNS
clients or servers. **NOTE:** all fields of the `ResourceRecord` object must be filled and the name must be a FQDN;
otherwise this method will throw a `\Badcow\DNS\UnsetValueException`.

Similarly, `ResourceRecord::fromWire(string $encoded, int &$offset = 0)` will construct a `ResourceRecord` object from
an encoded, "wire-formatted" string.