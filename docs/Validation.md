Validation
==========

Very little validation is done automatically when setting RData or Resource Record objects, so the onus is on the
implementor to validate the resource data and the zone record. Contained within the library, however, are some useful
validation tools. These are static functions contained within `Badcow\DNS\Validator` (here, simply referred to as
`Validator`).

## Validate the Zone
`Validator::zone()` will inspect a number of properties of a Zone, namely:
 * There is exactly one SOA record,
 * There are NS records, and
 * There is exactly one record class (while it is permissible to use obsolete classes such as CHAOS, in all modern
 contexts, this is always IN).

The return value is a binary sum of error codes which can be determined using boolean operands. A valid zone will
return `0`, AKA `Validator::ZONE_OKAY`. _Exempli gratia:_
```php
$zone //Some Badcow\DNS\Zone
if (Validator::zone($zone) & Validator::ZONE_NO_SOA) echo "There are no SOA Records";
if (Validator::zone($zone) & Validator::ZONE_TOO_MANY_CLASSES) echo "There are too many classes."; 
```

The return codes are:
 * `ZONE_NO_SOA`
 * `ZONE_TOO_MANY_SOA`
 * `ZONE_NO_NS`
 * `ZONE_NO_CLASS`
 * `ZONE_TOO_MANY_CLASSES`
 * `ZONE_OKAY`

