CHANGELOG for 4.x
=================
## 4.0
* `Badcow\DNS\Rdata\RdataInterface::fromWire` is no longer static and does not return an instance of the class. You now
must instantiate the object first, and then call `fromWire` method. All parameters are the same.
* `Badcow\DNS\Rdata\RdataInterface::fromText` is no longer static and does not return an instance of the class. You now
must instantiate the object first, and then call `fromText` method. All parameters are the same.
* Deleted `Bacow\DNS\Rdata\Algorithms`, use `Bacow\DNS\Algorithms` instead. 
* [PR #73](https://github.com/Badcow/DNS/pull/73) Resolves issue where records with integers are not parsed correctly. (Thank you, [Hossein Taleghani](https://github.com/a3dho3yn))
* [Issue #75](https://github.com/Badcow/DNS/issues/75) - Resolves issue where RRSIG records are not process correctly. (Thank you, [emkookmer](https://github.com/emkookmer))
