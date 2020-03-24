CHANGELOG for 4.x
=================
## 4.0
* `Badcow\DNS\Rdata\RdataInterface::fromWire` is no longer static and does not return an instance of the class. You now
must instantiate the object first, and then call `fromWire` method. All parameters are the same.