CHANGELOG for 2.x
=================
## Unreleased
* Comments are now parsed into Resource Record objects [Issue #30](https://github.com/Badcow/DNS/issues/30).
* Namespace `Badcow\DNS\Rdata\DNSSEC` is deprecated and will be removed in v3.0. [Issue #42](https://github.com/Badcow/DNS/issues/42)

## 2.0 - 2.3
* Removed `Rdata\FormattableInterface`, all aligned formatting is handled by `AlignedBuilder`.
* Removed `ZoneInterface`, `ResourceRecordInterface`, and `ZoneBuilderInterface`.
* `AlignedBuilder::build()` and `ZoneBuilder::build()` are static methods, no need to instantiate the builder.
* All methods now type-hint the return value.
* `Zone` implements `IteratorAggregate` and `Countable` so you can loop over `ResourceRecords` using
`foreach ($zone as $rr){}`.
* `Zone::expand()` method has been moved to `ZoneBuilder` and renamed as `ZoneBuilder::fillOutZone()`.
* Methods in `Validator` have been renamed to better reflect what they are validating.
* Added CAA Rdata type.
