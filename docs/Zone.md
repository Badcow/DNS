The DNS Zone Class
==================
> A DNS zone is any distinct, contiguous portion of the domain name space in the Domain Name System (DNS) for which administrative responsibility has been delegated to a single manager.
>
>\- _[Wikipedia: DNS Zone](https://en.wikipedia.org/wiki/DNS_zone)_

The `Badcow\DNS\Zone` class is simply a collection of `Badcow\DNS\ResourceRecord` objects common to a single DNS Zone.
The `Zone` object has three properties:
- `name`: the name of the zone (e.g. "example.com.")
- `defaultTtl`: The default time-to-live. If there is no TTL defined on the constituent Resource Records, then they
default to this property.
- `resourceRecords`: An array of `ResourceRecord` objects.

These properties can be optionally set in the `Zone` constructor:
```php
$resourceRecords = array(...);
$zone = new Zone('example.com.' 10800, $resourceRecords); 
```

## Property Accessors
The zone name, default TTL, and array of ResourceRecords can be accessed with simple getters:
```php
echo $zone->getName();
echo $zone->getDefaultTtl();
print_r($zone->getResourceRecords());
```

The `Zone` object implements `\ArrayAccess` so the `ResourceRecord` collection can be accessed like any other array:
```php
$rr_1 = $zone[0];
$rr_2 = $zone[1];
```
Similarly, `ResourceRecord` objects can be set and unset like any array:
```php
unset($zone[2]);
$zone[3] = $rr_4;
```

## Property Assigners
The zone name and default TTL can be assigned with simple setters:
```php
$zone->setName('test.com.'); //Note: this is a fully qualified domain name!
$zone->setDefaultTtl(3600);
```
There are three methods used for setting Resource Records:
- `$zone->fromArray($rr_array);` This takes an array of `ResourceRecord` objects and adds them to the Zone.
- `$zone->fromList($rr1, $rr2, $rr3, ...);` This takes a list of `ResourceRecord` objects and adds each of them to the Zone.
- `$zone->addResourceRecord($rr);` This adds a single `ResourceRecord` to the Zone.

**Note:** None of these methods overwrite the existing Resource Records in the Zone, they all append objects.

### Removing Resource Records
Resource Records can be removed from the Zone using the `remove` method:
```php
$zone = new Zone();
$resourceRecord = new Badcow\DNS\ResourceRecord();
$zone->addResourceRecord($resourceRecord);
echo count($zone); //Echos "1".
$zone->remove($resourceRecord);
echo count($zone); //Echos "0".
```

## Iteration
`Zone` implements `\IteratorAggregate` for iterating over each `ResourceRecord`:
```php
foreach ($zone as $i => $resourceRecord) {
    echo $resourceRecord->getName()."\n";
}
```

## Countable
`Zone` implements `\Countable`:
```php
count($zone); //Returns the number of ResourceRecords
$zone->count(); //Returns the number of ResourceRecords
```

## Other Methods:
- `Zone::getClass(): string` - Returns the first non-null Resource Record class (IN, CH, CS, or HS). If all Reource Record
classes are null, it will return IN by default.
- `Zone::contains(ResourceRecord $resourceRecord): bool` - Whether the Zone contains a particular Resource Record.
- `Zone::isEmpty(): bool` - Whether or not the Zone's Resource Record array is empty.