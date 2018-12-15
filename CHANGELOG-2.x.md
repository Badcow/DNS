CHANGELOG for 2.x
=================
* Removed `Rdata\FormattableInterface`, all aligned formatting is handled by `AlignedBuilder`.
* Removed `ZoneInterface`, `ResourceRecordInterface`, and `ZoneBuilderInterface`.
* `AlignedBuilder::build()` and `ZoneBuilder::build()` are static methods, no need to instantiate the builder.
* All methods now type-hint the return value.
* `Zone` implements `IteratorAggregate` and `Countable` so you can loop over `ResourceRecords` using
`foreach ($zone as $rr){}`.
* `Zone::expand()` method has been moved to `ZoneBuilder` and renamed as `ZoneBuilder::fillOutZone()`.
* Methods in `Validator` have been renamed to better reflect what they are validating.
